<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\Survey;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use PHPUnit_Framework_Assert as PHPUnit;
use Ramsey\Uuid\Uuid;
use Zend\Http\Response;

class SurveyContext implements Context
{
    const VALID_SURVEY_RESPONSE = 3;

    const ERROR_MESSAGE_INVALID_SURVEY_TOKEN = "Survey token is not valid";

    /** @var MotTestContext */
    private $motTestContext;

    /** @var MotTest */
    private $motTest;

    /** @var TestSupportHelper $testSupportHelper */
    private $testSupportHelper;

    /** @var Survey $survey */
    private $survey;

    /** @var MotTestData  */
    private $motTestData;

    /** @var  UserData */
    private $userData;

    /** @var SessionContext $sessionContext */
    private $sessionContext;

    /** @var string $generatedSurveyToken */
    private $generatedSurveyToken;

    /** @var string $generatedDuplicateSurveyToken */
    private $generatedDuplicateSurveyToken;

    /** @var int $satisfactionRating */
    private $satisfactionRating;

    /** @var Response $submittedSurveyHTTPResponse */
    private $submittedSurveyHTTPResponse;

    /** @var array $satisfactionRatings */
    private $satisfactionRatings;

    public function __construct(
        MotTest $motTest, Survey $survey,
        TestSupportHelper $testSupportHelper,
        MotTestData $motTestData,
        UserData $userData
    )
    {
        $this->testSupportHelper = $testSupportHelper;
        $this->motTest = $motTest;
        $this->survey = $survey;
        $this->motTestData = $motTestData;
        $this->userData = $userData;
    }

    /**
     * @BeforeScenario
     *
     * @var BeforeScenarioScope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->motTestContext = $scope->getEnvironment()->getContext(MotTestContext::class);
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
    }

    /**
     * @Given A survey has been completed
     */
    public function surveyCompleted()
    {
        $this->motTestContext->anMotHasBeenPassed();
        $this->generateSurveyToken();
        $this->submitSurveyResponse(3);

        $numberOfSurveys = $this->testSupportHelper->getGdsSurveyService()->getNumberOfSurveysCompleted();

        PHPUnit::assertNotEquals(0, $numberOfSurveys);
    }

    /**
     * @Given a survey token has been generated
     */
    public function generateSurveyToken()
    {
        $this->generatedSurveyToken = Uuid::uuid1();
        $this->testSupportHelper->getGdsSurveyService()
            ->persistTokenToDb(
                $this->generatedSurveyToken,
                $this->motTestData->getLast()->getId()
            );
    }


    /**
     * @Given /^There exist survey responses of ([0-9]) ([0-9]) ([0-9]) ([0-9]) ([0-9])$/
     *
     * @param int $rating1
     * @param int $rating2
     * @param int $rating3
     * @param int $rating4
     * @param int $rating5
     */
    public function thereExistSurveyResponsesOf($rating1, $rating2, $rating3, $rating4, $rating5)
    {
        $this->satisfactionRatings['rating1'] = $rating1;
        $this->satisfactionRatings['rating2'] = $rating2;
        $this->satisfactionRatings['rating3'] = $rating3;
        $this->satisfactionRatings['rating4'] = $rating4;
        $this->satisfactionRatings['rating5'] = $rating5;

        foreach ($this->satisfactionRatings as $rating) {
            $this->sessionContext->iAmLoggedInAsATester();
            $this->motTestContext->iHavePassedAnMotTest();
            $this->generateSurveyToken();
            $this->submitSurveyResponse($rating);
        }
    }

    /**
     * @When I submit a survey response of :satisfactionRating
     *
     * @param int $satisfactionRating
     */
    public function submitSurveyResponse($satisfactionRating)
    {
        if ($satisfactionRating == "null") {
            $this->satisfactionRating = null;
        }
        $this->satisfactionRating = $satisfactionRating;

        $this->submittedSurveyHTTPResponse = $this->motTest->submitSurveyResponse(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $satisfactionRating,
            $this->generatedSurveyToken
        );
    }

    /**
     * @When I submit a survey response using an invalid token
     *
     */
    public function submitSurveyWithInvalidToken()
    {
        $this->submitInvalidSurveyResponse($this::VALID_SURVEY_RESPONSE, Uuid::uuid1());
    }

    /**
     * @When I submit a survey response with a null token
     *
     */
    public function submitSurveyWithNullToken()
    {
        $this->submitInvalidSurveyResponse($this::VALID_SURVEY_RESPONSE, null);
    }

    /**
     * @When I submit a survey response with a duplicate token
     *
     */
    public function submitSurveyWithDuplicateToken()
    {
        $duplicateToken = $this->generateDuplicateToken();
        $this->submitInvalidSurveyResponse($this::VALID_SURVEY_RESPONSE, $duplicateToken);
    }

    /**
     * Utility method to submit survey with invalid response and / or token
     *
     * @param int $satisfactionRating
     *
     * @param int $token
     */
    private function submitInvalidSurveyResponse($satisfactionRating, $token)
    {
        $this->submittedSurveyHTTPResponse = $this->motTest->submitSurveyResponse(
            $this->sessionContext->getCurrentAccessToken(),
            $satisfactionRating,
            $token
        );
    }

    /**
     * Utility method to submit valid survey and assign the already used token as a duplicate
     *
     */
    private function generateDuplicateToken()
    {
        $this->sessionContext->iAmLoggedInAsATester();
        $this->motTestContext->iHavePassedAnMotTest();
        $this->generateSurveyToken();
        $this->submitSurveyResponse($this::VALID_SURVEY_RESPONSE);

        //Assign token already used above as duplicate
        $this->generatedDuplicateSurveyToken = $this->generateSurveyToken();
    }


    /**
     * @Then a BadRequestException will be thrown
     */
    public function assertBadRequestExceptionIsThrown()
    {
        $errorMessage = $this->submittedSurveyHTTPResponse->getBody()['errors'][0]['message'];
        PHPUnit::assertEquals($this::ERROR_MESSAGE_INVALID_SURVEY_TOKEN, $errorMessage);
    }

    /**
     * @Then the survey will not be displayed to the user
     */
    public function surveyNotDisplayedToUser()
    {
        $motTestDetails = $this->motTestData->getLastResponse()->getBody()->getData();
        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $apiResult = $this->survey->surveyIsDisplayed($token, $motTestDetails);

        PHPUnit::assertEquals(false, $apiResult);
    }

    /**
     * @Given /^I want to generate a survey report$/
     */
    public function generateSurveyReport()
    {
        $this->motTest->generateSurveyReports(
            $this->sessionContext->getCurrentAccessToken()
        );
    }

    /**
     * @Then /^I can download the report$/
     */
    public function iCanDownloadTheReport()
    {
        $reportResponse = $this->motTest->getSurveyReports(
            $this->sessionContext->getCurrentAccessToken()
        );

        PHPUnit::assertNotNull($reportResponse->getBody()->getData());
    }

    /**
     * @Then /^The survey response is saved$/
     */
    public function theSurveyResponseIsSaved()
    {
        PHPUnit::assertSame(Response::STATUS_CODE_200, $this->submittedSurveyHTTPResponse->getStatusCode());
        if (is_int((int) $this->satisfactionRating)) {
            PHPUnit::assertTrue(
                $this->satisfactionRating ==
                $this->submittedSurveyHTTPResponse->getBody()['data']['satisfaction_rating']
            );
        } else {
            PHPUnit::assertNull($this->submittedSurveyHTTPResponse->getBody()['satisfaction_rating']);
        }
    }

    /**
     * Retrieve details of most recent MOT test and format them for endpoint to determine if survey should be displayed.
     *
     * @return array
     */
    public function getMotTestDetailsForSurveyCheck()
    {
        return $this->motTestContext->getMotStatusData()->getBody()->getData();
    }
}
