<?php

namespace AccountTest\Action\PasswordReset;

use Account\Action\PasswordReset\AnswerSecurityQuestionsAction;
use Account\Form\SecurityQuestionAnswersForm;
use Account\Service\SecurityQuestionService;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\InputFilter\Account\SecurityQuestionAnswersInputFilter;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;
use Zend\Http\Request;
use Zend\Mvc\Controller\Plugin\Url;
use Zend\Stdlib\Parameters;

class AnswerSecurityQuestionsActionTest extends TestCase
{
    const PERSON_ID = 9;
    const QUESTION_ONE_ID = 2;
    const QUESTION_TWO_ID = 4;

    const FIELD_NAME_FIRST_ANSWER = SecurityQuestionAnswersInputFilter::FIELD_NAME_FIRST_ANSWER;
    const FIELD_NAME_SECOND_ANSWER = SecurityQuestionAnswersInputFilter::FIELD_NAME_SECOND_ANSWER;

    /** @var SecurityQuestionService|MockObject $securityQuestionService */
    private $securityQuestionService;

    public function setUp()
    {
        $this->securityQuestionService = XMock::of(SecurityQuestionService::class);
    }

    public function testNoRedirectWhenUserInputIsInvalid()
    {
        $parameters = $this->getPostParametersWithAnswers('', '');

        $this->withDefaultQuestions();

        $actionResult = $this->executeAction($parameters);

        $this->assertInstanceOf(ViewActionResult::class, $actionResult);
        $this->assertNoRedirect($actionResult);

        /** @var SecurityQuestionAnswersForm $form */
        $form = $actionResult->getViewModel()->getForm();
        $this->assertFalse($form->isValid());

        $this->assertFormFieldHasEmptyMessage($actionResult, self::FIELD_NAME_FIRST_ANSWER);
        $this->assertFormFieldHasEmptyMessage($actionResult, self::FIELD_NAME_SECOND_ANSWER);
    }

    public function testEmailRetrievedAndRedirectsWhenCorrectAnswersAreInput()
    {
        $parameters = $this->getPostParametersWithAnswers();

        $this
            ->withDefaultQuestions()
            ->withCorrectAnswers();

        $this->securityQuestionService
            ->expects($this->any())
            ->method('resetPersonPassword')
            ->willReturn('email@email.com');

        /** @var RedirectToRoute $actionResult */
        $actionResult = $this->executeAction($parameters);

        $this->assertRedirect($actionResult);

        $this->assertEquals('forgotten-password/confirmationEmail', $actionResult->getRouteName());
        $this->assertEquals('email@email.com', $actionResult->getFlashMessages()[0]->getContent());
    }

    public function testNoRedirectWhenUserInputIsValidButBothAnswersIncorrect()
    {
        $parameters = $this->getPostParametersWithAnswers();

        $this
            ->withDefaultQuestions()
            ->withBothAnswersIncorrect()
            ->withRemainingAttempts()
            ->expectPasswordWillNotBeReset();

        $actionResult = $this->executeAction($parameters);

        $this->assertNoRedirect($actionResult);

        $this->assertFormFieldHasFailedVerificationMessage($actionResult, self::FIELD_NAME_FIRST_ANSWER);
        $this->assertFormFieldHasFailedVerificationMessage($actionResult, self::FIELD_NAME_SECOND_ANSWER);
    }

    public function testCorrectErrorMessageDisplayedWhenOneAttemptLeftAndInputBothAnswersIncorrectly()
    {
        $parameters = $this->getPostParametersWithAnswers();

        $this
            ->withDefaultQuestions()
            ->withBothAnswersIncorrect()
            ->expectPasswordWillNotBeReset();

        $this->withRemainingAttempts(1);

        /** @var ViewActionResult $actionResult */
        $actionResult = $this->executeAction($parameters);
        $messages = $actionResult->getViewModel()->getValidationMessages();

        $this->assertContains([SecurityQuestionAnswersInputFilter::MSG_LAST_ATTEMPT_WARNING], $messages);
    }

    public function testRedirectWhenBothAnswersAreIncorrectAndNoAttemptsRemaining()
    {
        $parameters = $this->getPostParametersWithAnswers();

        $this
            ->withDefaultQuestions()
            ->withBothAnswersIncorrect()
            ->withNoRemainingAttempts()
            ->expectPasswordWillNotBeReset();

        /** @var RedirectToRoute $actionResult */
        $actionResult = $this->executeAction($parameters);

        $this->assertRedirect($actionResult);

        $this->assertEquals('forgotten-password/notAuthenticated', $actionResult->getRouteName());
    }

    public function testRedirectWhenBothAnswersAreCorrectButPasswordResetThrowsRuntimeException()
    {
        $parameters = $this->getPostParametersWithAnswers();

        $this
            ->withDefaultQuestions()
            ->withCorrectAnswers();

        $this->securityQuestionService
            ->expects($this->any())
            ->method('resetPersonPassword')
            ->willThrowException(new RuntimeException());

        $actionResult = $this->executeAction($parameters);

        $this->assertRedirect($actionResult);

        /** @var RedirectToRoute $actionResult */
        $this->assertEquals('forgotten-password/notAuthenticated', $actionResult->getRouteName());
    }

    public function testNoRedirectWhenUserInputIsValidButOneAnswerIncorrect()
    {
        $parameters = $this->getPostParametersWithAnswers();

        $this
            ->withDefaultQuestions()
            ->withFirstAnswerIncorrect()
            ->withRemainingAttempts()
            ->expectPasswordWillNotBeReset();

        $actionResult = $this->executeAction($parameters);

        $this->assertNoRedirect($actionResult);

        $this->assertFormFieldHasFailedVerificationMessage($actionResult, self::FIELD_NAME_FIRST_ANSWER);
        $this->assertFormFieldHasNoFailedVerificationMessage($actionResult, self::FIELD_NAME_SECOND_ANSWER);
    }

    public function testFormCreatedAndNoValidationMessagesWhenFirstLoaded()
    {
        $this->withDefaultQuestions();

        /** @var ViewActionResult $actionResult */
        $actionResult = $actionResult = $this->executeActionNoAnswers();

        $this->assertNoRedirect($actionResult);
        $this->assertEmpty($actionResult->getViewModel()->getValidationMessages());
        $this->assertInstanceOf(SecurityQuestionAnswersForm::class, $actionResult->getViewModel()->getForm());
    }

    private function withCorrectAnswers()
    {
        $this->securityQuestionService
            ->expects($this->any())
            ->method('verifyAnswers')
            ->willReturn([]);

        $this->securityQuestionService
            ->expects($this->any())
            ->method('isVerified')
            ->willReturn(true);

        return $this;
    }

    private function withBothAnswersIncorrect()
    {
        $this->securityQuestionService
            ->expects($this->any())
            ->method('verifyAnswers')
            ->willReturn([
                self::QUESTION_ONE_ID => SecurityQuestionAnswersInputFilter::MSG_FAILED_VERIFICATION,
                self::QUESTION_TWO_ID => SecurityQuestionAnswersInputFilter::MSG_FAILED_VERIFICATION
            ]);

        $this->securityQuestionService
            ->expects($this->any())
            ->method('isVerified')
            ->willReturn(false);

        return $this;
    }

    private function withFirstAnswerIncorrect()
    {
        $this->securityQuestionService
            ->expects($this->any())
            ->method('verifyAnswers')
            ->willReturn([
                self::QUESTION_ONE_ID => SecurityQuestionAnswersInputFilter::MSG_FAILED_VERIFICATION
            ]);

        $this->securityQuestionService
            ->expects($this->any())
            ->method('isVerified')
            ->willReturn(false);

        return $this;
    }

    private function withDefaultQuestions()
    {
        $this->securityQuestionService
            ->expects($this->any())
            ->method('getQuestionsForPerson')
            ->willReturn([new SecurityQuestionDto(), new SecurityQuestionDto()]);

        return $this;
    }

    private function assertRedirect($actual)
    {
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
    }

    private function assertNoRedirect($actual)
    {
        $this->assertNotInstanceOf(RedirectToRoute::class, $actual);
    }

    private function assertFormFieldHasFailedVerificationMessage(ViewActionResult $actionResult, $fieldName)
    {
        $this->assertFormFieldHasMessage(
            $actionResult,
            $fieldName,
            SecurityQuestionAnswersInputFilter::MSG_FAILED_VERIFICATION
        );
    }

    private function assertFormFieldHasNoFailedVerificationMessage(ViewActionResult $actionResult, $fieldName)
    {
        $messages = $actionResult->getViewModel()->getValidationMessages();
        $this->assertNotContains($fieldName, $messages);
    }

    private function assertFormFieldHasEmptyMessage(ViewActionResult $actionResult, $fieldName)
    {
        $this->assertFormFieldHasMessage(
            $actionResult,
            $fieldName,
            SecurityQuestionAnswersInputFilter::MSG_IS_EMPTY
        );
    }

    private function assertFormFieldHasMessage(ViewActionResult $actionResult, $fieldName, $message)
    {
        $messages = $actionResult->getViewModel()->getValidationMessages();
        $this->assertContains($message, $messages[$fieldName]);
    }

    private function getPostParametersWithAnswers($answer1 = 'answer', $answer2 = 'answer')
    {
        $postData = [
            SecurityQuestionAnswersInputFilter::FIELD_NAME_FIRST_QUESTION_ID => self::QUESTION_ONE_ID,
            SecurityQuestionAnswersInputFilter::FIELD_NAME_FIRST_ANSWER => $answer1,
            SecurityQuestionAnswersInputFilter::FIELD_NAME_SECOND_QUESTION_ID => self::QUESTION_TWO_ID,
            SecurityQuestionAnswersInputFilter::FIELD_NAME_SECOND_ANSWER => $answer2
        ];

        return new Parameters($postData);
    }

    private function executeAction(Parameters $parameters)
    {
        $action = new AnswerSecurityQuestionsAction($this->securityQuestionService, []);
        $action
            ->setFormActionUrl('/url')
            ->setBackUrl('/url');
        $actionResult = $action->execute(self::PERSON_ID, $parameters);

        return $actionResult;
    }

    private function executeActionNoAnswers()
    {
        $action = new AnswerSecurityQuestionsAction($this->securityQuestionService, []);
        $action
            ->setFormActionUrl('/url')
            ->setBackUrl('/url');
        $actionResult = $action->executeNoAnswers(self::PERSON_ID);

        return $actionResult;
    }

    private function withRemainingAttempts($numberOfAttempts = 5)
    {
        $this->securityQuestionService
            ->expects($this->any())
            ->method('hasRemainingAttempts')
            ->willReturn(true);

        $this->securityQuestionService
            ->expects($this->any())
            ->method('getRemainingAttempts')
            ->willReturn($numberOfAttempts);

        return $this;
    }

    private function withNoRemainingAttempts()
    {
        $this->securityQuestionService
            ->expects($this->any())
            ->method('hasRemainingAttempts')
            ->willReturn(false);

        $this->securityQuestionService
            ->expects($this->any())
            ->method('getRemainingAttempts')
            ->willReturn(0);

        return $this;
    }

    private function expectPasswordWillNotBeReset()
    {
        $this->securityQuestionService
            ->expects($this->never())
            ->method('resetPersonPassword');

        return $this;
    }
}
