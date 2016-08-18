<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Api\Survey;
use PHPUnit_Framework_Assert as PHPUnit;

class SurveyContext implements Context
{
    /** @var MotTestContext */
    private $motTestContext;

    /** @var TestSupportHelper $testSupportHelper */
    private $testSupportHelper;

    /** @var Survey $survey */
    private $survey;

    /** @var SessionContext $sessionContext */
    private $sessionContext;

    /**
     * @param TestSupportHelper $testSupportHelper
     * @param Survey            $survey
     */
    public function __construct(Survey $survey, TestSupportHelper $testSupportHelper)
    {
        $this->testSupportHelper = $testSupportHelper;
        $this->survey = $survey;
    }

    /**
     * @BeforeScenario
     * @var BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->motTestContext = $scope->getEnvironment()->getContext(MotTestContext::class);
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
    }

    /**
     * @Given No survey has been completed
     */
    public function noSurveyCompleted()
    {
        $this->testSupportHelper->getGdsSurveyService()->deleteAllSurveys();
        
        $numberOfSurveys = $this->testSupportHelper->getGdsSurveyService()->getNumberOfSurveysCompleted();

        PHPUnit::assertEquals(0, $numberOfSurveys);
    }

    /**
     * @Given A survey has been completed
     */
    public function surveyCompleted($useCurrentTester = false)
    {
        $this->motTestContext->iSubmitASurveyResponse(3, $useCurrentTester);
        
        $numberOfSurveys = $this->testSupportHelper->getGdsSurveyService()->getNumberOfSurveysCompleted();

        PHPUnit::assertNotEquals(0, $numberOfSurveys);
    }

    /**
     * @Given A survey has been completed by that tester
     */
    public function surveyCompletedBySameTester()
    {
        $this->surveyCompleted(true);
    }

    /**
     * @Given the next normal MOT test should display the survey
     */
    public function createMotTestEntries()
    {
        $this->testSupportHelper->getGdsSurveyService()->generateMotTestsToDisplaySurveyOnNextTest();
    }

    /**
     * @Then the survey is displayed to the user
     */
    public function surveyDisplayedToUser()
    {
        $motTestDetails =  $this->motTestContext->getMotTestDetailsForSurveyCheck();
        $token = $this->sessionContext->getCurrentAccessToken();
        $apiResult = $this->survey->surveyIsDisplayed($token, $motTestDetails);

        PHPUnit::assertEquals(true, $apiResult);
    }

    /**
     * @Then the survey is not displayed to the user
     */
    public function surveyNotDisplayedToUser()
    {
        $motTestDetails =  $this->motTestContext->getMotTestDetailsForSurveyCheck();
        $token = $this->sessionContext->getCurrentAccessToken();
        $apiResult = $this->survey->surveyIsDisplayed($token, $motTestDetails);

        PHPUnit::assertEquals(false, $apiResult);
    }
}
