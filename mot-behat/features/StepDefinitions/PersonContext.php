<?php

use Behat\Behat\Context\Context;
use Dvsa\Mot\Behat\Support\Api\Notification;
use Dvsa\Mot\Behat\Support\Api\Person;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Tester;
use Dvsa\Mot\Behat\Support\Helper\ApiResourceHelper;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Response;
use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto\PerformanceDashboardStatsDto;
use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\PerformanceDashboardStatsApiResource;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Exception\UnauthorisedException;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class PersonContext implements Context, \Behat\Behat\Context\SnippetAcceptingContext
{
    private $personalMotTestingClasses;

    private $personalDashboard;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var Tester
     */
    private $tester;

    /**
     * @var Response
     */
    private $testerDetailsResponse;

    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @var Session
     */
    private $session;


    private $ae;

    /**
     * @var PerformanceDashboardStatsDto
     */
    private $personStats;

    private $authorisedExaminerData;

    private $siteData;

    private $userData;

    private $motTestData;

    private $apiResourceHelper;

    public function __construct(
        TestSupportHelper $testSupportHelper,
        Session $session,
        Person $person,
        Tester $tester,
        Notification $notification,
        AuthorisedExaminerData $authorisedExaminerData,
        SiteData $siteData,
        UserData $userData,
        MotTestData $motTestData,
        ApiResourceHelper $apiResourceHelper
    ) {
        $this->testSupportHelper = $testSupportHelper;
        $this->session = $session;
        $this->person = $person;
        $this->tester = $tester;
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->siteData = $siteData;
        $this->userData = $userData;
        $this->motTestData = $motTestData;
        $this->apiResourceHelper = $apiResourceHelper;
    }

    /**
     * @When /^I get Information about my MOT Classes$/
     */
    public function iGetInformationAboutMyMOTClasses()
    {
        $this->personalMotTestingClasses = $this->person->getPersonMotTestingClasses(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->userData->getCurrentLoggedUser()->getUserId()
        );
    }

    /**
     * @Then /^I will see my Available Classes$/
     */
    public function iWillSeeMyAvailableClasses()
    {
        $classes = $this->personalMotTestingClasses->getBody()->getData();
        foreach ($classes as $class => $status) {
            PHPUnit::assertEquals(AuthorisationForTestingMotStatusCode::QUALIFIED, $status, $class . ' not QLFD');
        }
    }

    /**
     * @When /^I get Information about my Dashboard$/
     */
    public function iGetInformationAboutMyDashboard()
    {
        $this->personalDashboard = $this->person->getPersonDashboard(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->userData->getCurrentLoggedUser()->getUserId()
        );
    }

    /**
     * @Then /^I will see my Dashboard Information$/
     */
    public function iWillSeeMyDashboardInformation()
    {
        // Check that the dashboard page returned a 200 status code
        PHPUnit::assertEquals(
            HttpResponse::STATUS_CODE_200,
            $this->personalDashboard->getStatusCode(),
            'Non-200 status code returned for dashboard'
        );

        // Check that the hero key in response data is 'tester'
        PHPUnit::assertEquals(
            'tester',
            $this->personalDashboard->getBody()->getData()['hero'],
            'Dashboard does not contain Tester'
        );
    }

    /**
     * @Then /^my Dashboard will return the MotTestNumber$/
     */
    public function myDashboardWillReturnTheMotTestNumber()
    {
        $inProgressTestNumber = $this->personalDashboard->getBody()->getData()['inProgressTestNumber'];
        $createdMotTestNumber = $this->motTestData->getLast()->getMotTestNumber();
        $message = 'In progress MOT test number %s does not match created MOT test number %s';

        PHPUnit::assertEquals(
            $inProgressTestNumber,
            $createdMotTestNumber,
            sprintf($message, $inProgressTestNumber, $createdMotTestNumber)
        );
    }

    /**
     * @When /^I get my Profile details$/
     */
    public function iGetMyProfileDetails()
    {
        $this->testerDetailsResponse = $this->tester->getTesterDetails(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->userData->getCurrentLoggedUser()->getUserId()
        );
    }

    /**
     * @Then /^I will see my username in my Profile$/
     */
    public function iWillSeeMyProfileDetails()
    {
        PHPUnit::assertEquals(
            $this->userData->getCurrentLoggedUser()->getUsername(),
            $this->testerDetailsResponse->getBody()->getData()[PersonParams::USERNAME],
            'Username not returned in response object.'
        );
    }

    /**
     * @Given /^I will see my user id in my Profile$/
     */
    public function iWillSeeMyUserIdInMyProfile()
    {
        PHPUnit::assertEquals(
            $this->userData->getCurrentLoggedUser()->getUserId(),
            $this->testerDetailsResponse->getBody()->getData()[PersonParams::ID],
            'User id not returned in response object.'
        );
    }

    /**
     * @Then /^my profile will contain the role "([^"]*)"$/
     *
     * @param $role
     *
     * @internal param $role
     */
    public function myProfileWillContainTheRole($role)
    {
        $roles = $this->testerDetailsResponse->getBody()->getData()['roles'];

        for ($x = 0; $x < count($roles); $x++) {
            if (stristr($roles[$x], $role) == true) {
                return;
            }
        }

        PHPUnit::assertEquals($role, $roles[$x], 'Role not returned in response object: '.$role);
    }

    /**
     * @Given The user :userRole exists
     */
    public function theUserExists($userRole)
    {
        $userService = $this->testSupportHelper->userRoleServiceFactory($userRole);
        $user = $userService->create([]);
        $authenticatedUser = $this->session->startSession(
            $user->data[PersonParams::USERNAME],
            $user->data[PersonParams::PASSWORD]
        );

        $this->userData->getAll()->add($authenticatedUser, $userRole);
    }

    private function getAuthorisationForTestingMotStatusCode($status)
    {
        switch ($status) {
            case "Unknown":
                $code = AuthorisationForTestingMotStatusCode::UNKNOWN;
                break;
            case "Initial Training Needed":
                $code = AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED;
                break;
            case "Demo Test Needed":
                $code = AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED;
                break;
            case "Qualified":
                $code = AuthorisationForTestingMotStatusCode::QUALIFIED;
                break;
            case "Refresher Needed":
                $code = AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED;
                break;
            case "Suspended":
                $code = AuthorisationForTestingMotStatusCode::SUSPENDED;
                break;
            default:
                throw new \InvalidArgumentException('Status \"' . $status . '\" not found');
        }

        return $code;
    }

    /**
     * @When I have a Tester Qualification status of :status for group :group
     *
     * @param $status
     * @param $group
     */
    public function iHaveATesterQualificationStatusForGroup($status, $group)
    {
        $this->iGetMyProfileDetails();

        $testerService = $this->testSupportHelper->getTesterService();
        $statusCode = $this->getAuthorisationForTestingMotStatusCode($status);

        $testerService->updateTesterQualificationStatus($this->userData->getCurrentLoggedUser()->getUserId(), $group, $statusCode);
    }

    /**
     * @When I get my person stats
     */
    public function iGetMyPersonalStats()
    {
        $user = $this->userData->getCurrentLoggedUser();

        /** @var PerformanceDashboardStatsApiResource $apiResource */
        $apiResource = $this->apiResourceHelper->create(PerformanceDashboardStatsApiResource::class);
        $this->personStats = $apiResource->getStats($user->getUserId());
    }

    /**
     * @Then person day stats show :conductedTests conducted tests :passedNormalTests passed tests and :failedNormalTests failed tests
     */
    public function personDayStatsShowCorrectTestCount($conductedTests, $passedNormalTests, $failedNormalTests)
    {
        $data = $this->personStats->getDayStats();

        PHPUnit::assertEquals($conductedTests, $data->getTotal());
        PHPUnit::assertEquals($passedNormalTests, $data->getNumberOfPasses());
        PHPUnit::assertEquals($failedNormalTests, $data->getNumberOfFails());
    }


    /**
     * @Then person month stats show :conductedTests conducted tests :passedNormalTests passed tests and :failedNormalTests failed tests and :failRage fail rate
     */
    public function personMonthStatsShowCorrectTestCount($conductedTests, $passedNormalTests, $failedNormalTests, $failRage)
    {
        $data = $this->personStats->getMonthStats();

        PHPUnit::assertEquals($conductedTests, $data->getTotalTestsCount());
        PHPUnit::assertEquals($passedNormalTests, $data->getPassedTestsCount());
        PHPUnit::assertEquals($failedNormalTests, $data->getFailedTestsCount());
        PHPUnit::assertEquals($failRage, $data->getFailRate());
    }

    /**
     * @Then /^the my profile details are returned$/
     */
    public function theMyProfileDetailsAreReturned()
    {
        $currentUsername = $this->userData->getCurrentLoggedUser()->getUsername();
        $retrievedUsername = $this->testerDetailsResponse->getBody()->getData()[PersonParams::USERNAME];
        PHPUnit::assertEquals($retrievedUsername, $currentUsername);
    }

    /**
     * @When /^I try to create a new AE$/
     */
    public function iTryToCreateANewAE()
    {
        $this->iGetMyProfileDetails();
        $username =  $this->testerDetailsResponse->getBody()->getData()[PersonParams::USERNAME];
        $user = $this->userData->get($username);
        try {
            $this->authorisedExaminerData->createByUser($user, "Organisation Ltd");
            $this->ae = "CREATED";
        } catch (UnauthorisedException $ue) {
            $this->ae = "FORBIDDEN";
        }
    }

    /**
     * @Then /^the creation of AE will be (.*)$/
     */
    public function theCreationOfAEWillBe($expectedStatus)
    {
        PHPUnit::assertEquals($this->ae, $expectedStatus);
    }

    /**
     * @Then /^my Dashboard should show I have a non-MOT Test in progress$/
     */
    public function myDashboardShouldShowIHaveANonMotTestInProgress()
    {
        PHPUnit::assertTrue(is_numeric($this->motTestData->getLast()->getMotTestNumber()), 'MOT test number is not numeric');
    }
}
