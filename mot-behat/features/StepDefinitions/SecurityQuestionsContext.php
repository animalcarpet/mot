<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Response;
use Dvsa\Mot\Behat\Support\Api\Session;
use PHPUnit_Framework_TestCase as PHPUnit;
use Dvsa\Mot\Behat\Support\Api\SecurityQuestionsChange;

class SecurityQuestionsContext implements Context
{
    // This is not a real security question but constant is here to help context
    const SECURITY_QUESTION_ID_NAME_OF_DOG = 1;
    const SECURITY_QUESTION_ID_FIRST_KISS = 2;

    /** @var SessionContext */
    private $sessionContext;

    /** @var array */
    private $securityQuestionData = [];

    /** @var SecurityQuestionsChange $securityQuestionChange */
    private $securityQuestionsChange;

    /** @var Response $response */
    private $response;

    /**
     * @param SecurityQuestionsChange $securityQuestionsChange
     * @param Session $session
     */
    public function __construct(SecurityQuestionsChange $securityQuestionsChange, Session $session)
    {
        $this->securityQuestionsChange = $securityQuestionsChange;
        $this->session = $session;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
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
        $userId = $this->sessionContext->getCurrentUserId();
        $this->response =
            $this->securityQuestionsChange->changeQuestions($userId, $this->securityQuestionData);
    }

    /**
     * @Then my questions have been updated
     */
    public function myQuestionsAreUpdated()
    {
        PHPUnit::assertEquals(200, $this->response->getStatusCode());
    }

    /**
     * @Then /^my questions have not been updated$/
     */
    public function myQuestionsHaveNotBeenUpdated()
    {
        PHPUnit::assertEquals(400, $this->response->getStatusCode());
    }

}