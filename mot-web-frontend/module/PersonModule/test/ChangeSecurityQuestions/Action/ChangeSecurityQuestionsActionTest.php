<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\ChangeSecurityQuestions\Action;

use Core\Action\ViewActionResult;
use Core\Action\RedirectToRoute;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionOneController;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Form\ChangeSecurityQuestionsPasswordForm;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsStepService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\PasswordValidationService;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\Request;
use Zend\Stdlib\ParametersInterface;

class ChangeSecurityQuestionsActionTest extends \PHPUnit_Framework_TestCase
{
    const TEST_STEP_ONE = 'step-one';
    const TEST_STEP_TWO = 'step-two';
    const TEST_REDIRECT_TO_QUESTION = 'mock-question-one';

    /** @var  ChangeSecurityQuestionsSessionService $changeSecurityQuestionsSessionService */
    private $changeSecurityQuestionsSessionService;

    /** @var  ChangeSecurityQuestionsStepService $changeSecurityQuestionsStepService */
    private $changeSecurityQuestionsStepService;

    /** @var Request $request */
    private $request;

    /** @var  PasswordValidationService $passwordValidationService */
    private $passwordValidationService;

    public function setUp()
    {
        parent::setUp();
        $this->changeSecurityQuestionsStepService = XMock::of(ChangeSecurityQuestionsStepService::class);
        $this->changeSecurityQuestionsSessionService = XMock::of(ChangeSecurityQuestionsSessionService::class);
        $this->request = XMock::of(Request::class);
        $this->passwordValidationService = XMock::of(PasswordValidationService::class);
    }

    public function testWhenPost_formValid_passwordValid_shouldRedirectToQuestionOne()
    {
        $this->changeSecurityQuestionsStepService
            ->expects($this->once())
            ->method('getSteps')
            ->willReturn([self::TEST_STEP_ONE, self::TEST_STEP_TWO]);

        $this->mockIsPost(true, $this->validPostData());

        $this->passwordValidationService
            ->expects($this->any())
            ->method('isPasswordValid')
            ->willReturn(true);

        $actionResult = $this->buildAction()->execute($this->request);

        $this->assertInstanceOf(RedirectToRoute::class, $actionResult);
        $this->assertSame(ChangeSecurityQuestionOneController::ROUTE, $actionResult->getRouteName());
    }

    public function testWhenPost_formValid_passwordInvalid_willShowErrorMessage()
    {
        $this->mockIsPost(true, $this->validPostData());

        $this->passwordValidationService
            ->expects($this->any())
            ->method('isPasswordValid')
            ->willReturn(false);

        $actionResult = $this->buildAction()->execute($this->request);

        $this->assertInstanceOf(ViewActionResult::class, $actionResult);
        $form = $actionResult->getViewModel()->getForm();
        $this->assertCount(1, $form->getMessages(ChangeSecurityQuestionsPasswordForm::FIELD_PASSWORD));
        $this->assertSame(ChangeSecurityQuestionsPasswordForm::MSG_PROBLEM_WITH_PASSWORD,
            $form->getMessages(ChangeSecurityQuestionsPasswordForm::FIELD_PASSWORD)[0]);
    }

    public function mockIsPost($isPost, $postData)
    {
        if ($isPost) {
            $params = XMock::of(ParametersInterface::class);
            $params->expects($this->once())
                ->method('toArray')
                ->willReturn($postData);

            $this->request->expects($this->once())->method('isPost')->willReturn($isPost);
            $this->request->expects($this->once())->method('getPost')->willReturn($params);
        } else {
            $this->request->expects($this->once())->method('isPost')->willReturn($isPost);
        }
    }

    public function buildAction()
    {
        $action = new ChangeSecurityQuestionsAction(
            $this->changeSecurityQuestionsStepService,
            $this->changeSecurityQuestionsSessionService,
            $this->passwordValidationService
        );
        return $action;
    }

    public function validPostData()
    {
        return [
            'Password' => 'password',
        ];
    }

    public function invalidPostData()
    {
        return [
            'Password' => '',
        ];
    }
}