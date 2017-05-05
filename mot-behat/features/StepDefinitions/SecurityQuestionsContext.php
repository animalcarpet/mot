<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\SecurityQuestionsAnswer;
use Dvsa\Mot\Behat\Support\Api\SecurityQuestionsChange;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Dvsa\Mot\Behat\Support\Data\UserData;
use PHPUnit_Framework_TestCase as PHPUnit;
use Zend\Http\Response as HttpResponse;

class SecurityQuestionsContext implements Context
{
    // This is not a real security question but constant is here to help context
    const SECURITY_QUESTION_ID_NAME_OF_DOG = 1;
    const SECURITY_QUESTION_ID_FIRST_KISS = 2;

    private $userData;
    private $securityQuestionData = [];
    private $securityQuestionsChange;
    private $securityQuestionsAnswer;

    public function __construct(
        SecurityQuestionsAnswer $securityQuestionsAnswer,
        SecurityQuestionsChange $securityQuestionsChange,
        Session $session,
        UserData $userData
    )
    {
        $this->securityQuestionsAnswer = $securityQuestionsAnswer;
        $this->securityQuestionsChange = $securityQuestionsChange;
        $this->session = $session;
        $this->userData = $userData;
    }

    /**
     * @Given /^I update my security question answers to be (.*) and (.*)$/
     */
    public function iUpdateMySecurityQuestionAnswersToBeAnd1($firstAnswer, $secondAnswer)
    {
        // The API expects question to be an id and not the actual question
        $this->setSecurityQuestionData(
            [
                [
                    'questionId' => self::SECURITY_QUESTION_ID_NAME_OF_DOG,
                    'answer' => $firstAnswer,
                ],
            [
                    'questionId' => self::SECURITY_QUESTION_ID_FIRST_KISS,
                    'answer' => $secondAnswer
                ]
            ]
        );
    }

    /**
     * Add the data into the securityQuestionsData
     * @param array $updatedQuestions
     */
    private function setSecurityQuestionData(array $updatedQuestions)
    {
        $this->securityQuestionData = $updatedQuestions;
    }

    /**
     * @When I confirm my changes to my security questions
     */
    public function iConfirmMyChangesToMySecurityQuestions()
    {
        $userId = $this->userData->getCurrentLoggedUser()->getUserId();
        $this->securityQuestionsChange->changeQuestions($userId, $this->securityQuestionData);
    }

    /**
     * @When I try confirm my changes to my security questions
     */
    public function iTryConfirmMyChangesToMySecurityQuestions()
    {
        try {
            $this->iConfirmMyChangesToMySecurityQuestions();
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @When I correctly answer my security questions
     */
    public function iCorrectlyAnswerMySecurityQuestions()
    {
        $userId = $this->userData->getLast()->getUserId();

        $this->securityQuestionsAnswer->answerQuestions($userId, [
            self::SECURITY_QUESTION_ID_NAME_OF_DOG => 'blah',
            self::SECURITY_QUESTION_ID_FIRST_KISS => 'blah'
        ]);
    }

    /**
     * @When I incorrectly answer my security questions
     */
    public function iIncorrectlyAnswerMySecurityQuestions()
    {
        $userId = $this->userData->getLast()->getUserId();

        $this->securityQuestionsAnswer->answerQuestions($userId, [
            self::SECURITY_QUESTION_ID_NAME_OF_DOG => 'wrong!',
            self::SECURITY_QUESTION_ID_FIRST_KISS => 'wrong!'
        ]);
    }

    /**
     * @Then my questions have been updated
     */
    public function myQuestionsAreUpdated()
    {
        $response = $this->securityQuestionsChange->getLastResponse();
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then /^my questions have not been updated$/
     */
    public function myQuestionsHaveNotBeenUpdated()
    {
        $response = $this->securityQuestionsChange->getLastResponse();
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_400, $response->getStatusCode());
    }

    /**
     * @Then my security questions are verified
     */
    public function mySecurityQuestionsAreVerified()
    {
        $response = $this->securityQuestionsAnswer->getLastResponse();
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());

        foreach ($response->getBody()->getData() as $isAnswerCorrect) {
            PHPUnit::assertTrue($isAnswerCorrect);
        }
    }

    /**
     * @Then my security questions are not verified
     */
    public function mySecurityQuestionsAreNotVerified()
    {
        $response = $this->securityQuestionsAnswer->getLastResponse();
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());

        foreach ($response->getBody()->getData() as $isAnswerCorrect) {
            PHPUnit::assertFalse($isAnswerCorrect);
        }
    }
}
