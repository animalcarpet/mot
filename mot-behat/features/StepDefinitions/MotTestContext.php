<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Api\ContingencyTest;
use Dvsa\Mot\Behat\Support\Api\DemoTest;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\NonMotTest;
use Dvsa\Mot\Behat\Support\Api\OdometerReading;
use Dvsa\Mot\Behat\Support\Api\ReasonForRejection;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Api\Vehicle;
use Dvsa\Mot\Behat\Support\Api\SlotReport;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\ContingencyMotTestData;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupA;
use Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejectionGroupB;
use Dvsa\Mot\Behat\Support\Data\Params\MotTestParams;
use Dvsa\Mot\Behat\Support\Data\Params\VehicleParams;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Data\Params\MeterReadingParams;
use Dvsa\Mot\Behat\Support\Data\Params\ContingencyDataParams;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Data\Params\BrakeTestResultParams;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Response;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Utility\ArrayUtils;
use Dvsa\Mot\Behat\Support\Data\ContingencyData;
use Dvsa\Mot\Behat\Support\History;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class MotTestContext implements Context, SnippetAcceptingContext
{
    const USERNAME_PREFIX_LENGTH = 20;

    /**
     * @var Response
     */
    private $motTestResponse;

    /**
     * @var Response
     */
    private $statusData;

    /**
     * @var string
     */
    private $inProgressMotTestNumber;

    /**
     * @var BrakeTestResult
     */
    private $brakeTestResult;

    /**
     * @var MotTest
     */
    private $motTest;

    /**
     * @var array
     */
    private $motTests;

    /**
     * @var DemoTest
     */
    private $demoTest;

    /**
     * @var NonMotTest
     */
    private $nonMotTest;

    /**
     * @var OdometerReading
     */
    private $odometerReading;

    /**
     * @var ReasonForRejection
     */
    private $reasonForRejection;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Vehicle
     */
    private $vehicle;

    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @var SessionContext
     */
    private $sessionContext;

    /**
     * @var VehicleContext
     */
    private $vehicleContext;

    /**
     * @var int
     */
    private $vehicleId;

    /**
     * @var ContingencyTestContext
     */
    private $contingencyTestContext;

    /**
     * @var ContingencyTest
     */
    private $contingencyTest;

    /**
     * @var PersonContext
     */
    private $personContext;

    /**
     * @var array
     */
    private $motTestNumbers;

    /**
     * @var History
     */
    private $history;

    /**
     * @var CertificateContext
     */
    private $certificateContext;

    /**
     * @var VtsContext
     */
    private $vtsContext;

    /**
     * @var AuthorisedExaminerContext
     */
    private $aeContext;

    /**
     * @var MotTestLogContext
     */
    private $motTestLogContext;

    /**
     * @var SlotReport
     */
    private $slotsReport;

    /**
     * @var OdometerReadingContext
     */
    private $odometerReadingContext;

    /**
     * @var BrakeTestResultContext
     */
    private $brakeTestResultContext;

    private $vehicleData;

    private $siteData;

    private $authorisedExaminerData;

    private $userData;

    private $motTestData;

    private $contingencyData;

    private $contingencyMotTestData;

    public function __construct(
        BrakeTestResult $brakeTestResult,
        MotTest $motTest,
        DemoTest $demoTest,
        ContingencyTest $contingencyTest,
        NonMotTest $nonMotTest,
        OdometerReading $odometerReading,
        ReasonForRejection $reasonForRejection,
        Session $session,
        TestSupportHelper $testSupportHelper,
        Vehicle $vehicle,
        History $history,
        SlotReport $slotsReport,
        VehicleData $vehicleData,
        SiteData $siteData,
        AuthorisedExaminerData $authorisedExaminerData,
        UserData $userData,
        MotTestData $motTestData,
        ContingencyData $contingencyData,
        ContingencyMotTestData $contingencyMotTestData
    ) {
        $this->brakeTestResult = $brakeTestResult;
        $this->motTest = $motTest;
        $this->demoTest = $demoTest;
        $this->contingencyTest = $contingencyTest;
        $this->nonMotTest = $nonMotTest;
        $this->odometerReading = $odometerReading;
        $this->reasonForRejection = $reasonForRejection;
        $this->session = $session;
        $this->testSupportHelper = $testSupportHelper;
        $this->vehicle = $vehicle;
        $this->history = $history;
        $this->slotsReport = $slotsReport;
        $this->vehicleData = $vehicleData;
        $this->siteData = $siteData;
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->userData = $userData;
        $this->motTestData = $motTestData;
        $this->contingencyData = $contingencyData;
        $this->contingencyMotTestData = $contingencyMotTestData;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->sessionContext = $scope->getEnvironment()->getContext(SessionContext::class);
        $this->vehicleContext = $scope->getEnvironment()->getContext(VehicleContext::class);
        $this->contingencyTestContext = $scope->getEnvironment()->getContext(ContingencyTestContext::class);
        $this->personContext = $scope->getEnvironment()->getContext(PersonContext::class);
        $this->certificateContext = $scope->getEnvironment()->getContext(CertificateContext::class);
        $this->vtsContext = $scope->getEnvironment()->getContext(VtsContext::class);
        $this->aeContext = $scope->getEnvironment()->getContext(AuthorisedExaminerContext::class);
        $this->motTestLogContext = $scope->getEnvironment()->getContext(MotTestLogContext::class);
        $this->odometerReadingContext = $scope->getEnvironment()->getContext(OdometerReadingContext::class);
        $this->brakeTestResultContext = $scope->getEnvironment()->getContext(BrakeTestResultContext::class);
    }

    /**
     * @Then /^the MOT Test Status is "([^"]*)"$/
     * @Then /^the MOT Test Status should be "([^"]*)"$/
     *
     * @param $status
     */
    public function theMOTTestStatusIs($status)
    {
        $actualStatus = $this->motTestData->getAll()->last()->getStatus();

        PHPUnit::assertEquals($status, $actualStatus, 'MOT Test Status is incorrect');
    }

    /**
     * @Given a logged in Tester, starts an MOT Test
     * @Given I start an MOT test as a Tester
     * @param bool $useCurrentTester - use the currently logged in tester instead of a new one
     */
    public function iStartMotTestAsTester($useCurrentTester = false)
    {
        if (!$useCurrentTester) {
            $this->sessionContext->iAmLoggedInAsATester();
        }

        $this->motTestData->create(
            $this->userData->getCurrentLoggedUser(),
            $this->vehicleData->create(),
            $this->siteData->get()
            );
    }

    public function startMotTest($userId, $token, $motTestParams = [], $vehicleId = null)
    {
        $testClass = VehicleClassCode::CLASS_4;

        if (is_null($vehicleId)) {
            $vehicle  = $this->vehicleData->createWithVehicleClass($token, $testClass);
        } else {
            /** @var VehicleDto $vehicle */
            $vehicle = $this->vehicleData->getAll()->filter(function (VehicleDto $vehicle) use ($vehicleId){
                return $vehicle->getId() === $vehicleId;
            })->first();
        }

        $collection = $this->userData->getAll()->filter(function (AuthenticatedUser $user) use ($userId){
            return $user->getUserId() === $userId;
        });

        $user = $collection->first();

        $type = ArrayUtils::tryGet($motTestParams, "motTestType", MotTestTypeCode::NORMAL_TEST);

        return $this->motTestData->create(
            $user,
            $vehicle,
            $this->siteData->get(),
            $type
        );
    }

    /**
     * @When I start a Demo MOT Test
     */
    public function iStartDemoTest()
    {
        $vehicle = $this->vehicleData->create();
        $this->motTestData->create(
            $this->userData->getCurrentLoggedUser(),
            $vehicle,
            null,
            MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING
        );
    }

    /**
     * @When I start a non-MOT Test
     */
    public function iStartNonMotTest()
    {
        $vehicleId = $this->vehicleContext->createVehicle();

        $this->motTestData = $this->nonMotTest->startMotTest(
            $this->sessionContext->getCurrentAccessToken(),
            $vehicleId
        );
    }

    /**
     * @When I start an MOT Test
     */
    public function iStartMotTest()
    {
        $vehicle = $this->vehicleData->createByUser($this->userData->getCurrentLoggedUser()->getAccessToken());
        $this->motTestData->create(
            $this->userData->getCurrentLoggedUser(),
            $vehicle,
            $this->siteData->get()
        );
    }

    /**
     * @Given I start a Demo MOT test as a Tester
     */
    public function iStartDemoMotTestAsTester()
    {
        $this->sessionContext->iAmLoggedInAsATester();
        $this->iStartDemoTest();
    }

    /**
     * @Given /^the Tester adds a Reason for Rejection$/
     */
    public function theTesterAddsAReasonForRejection()
    {
        $motTest = $this->motTestData->getAll()->last();
        if ($motTest->getVehicleClass()->getCode() < VehicleClassCode::CLASS_3) {
            $rfrId = ReasonForRejectionGroupA::RFR_BRAKE_HANDLEBAR_LEVER;
        } else {
            $rfrId = ReasonForRejectionGroupB::RFR_BODY_STRUCTURE_CONDITION;
        }

        $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        $response = $this->reasonForRejection->addFailure($token, $motTest->getMotTestNumber(), $rfrId);
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then /^I can search for Rfr$/
     */
    public function iCanSearchForRfr()
    {
        $response = $this->reasonForRejection->search(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $this->motTestData->getAll()->last()->getMotTestNumber(),
            "brake", 0, 2
        );
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then /^I can list child test items selector$/
     */
    public function iCanListChildTestItemSelector()
    {
        /** @var MotTestDto $mot */
        $mot = $this->motTestData->getAll()->last();

        $response = $this->reasonForRejection->listTestItemSelectors(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $mot->getMotTestNumber()
        );
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @When /^the Tester Fails the Mot Test$/
     */
    public function theTesterFailsTheMotTest()
    {
        try {
            $this->motTestData->failMotTest($this->motTestData->getAll()->last());
        } catch (\Exception $e) {

        }

    }

    /**
     * @When the Tester Passes the Mot Test
     */
    public function theTesterPassesTheMotTest()
    {
        try {
            $this->motTestData->passMotTest($this->motTestData->getAll()->last());
        } catch (\Exception $e) {

        }

    }

    /**
     * @When /^I have an MOT Test In Progress$/
     */
    public function iHaveAnMOTTestInProgress()
    {
        $this->iStartAnMotTestWithAClassVehicle(VehicleClassCode::CLASS_4);
    }

    /**
     * @When /^I have a Demo MOT Test In Progress$/
     */
    public function iHaveADemoMOTTestInProgress()
    {
        $this->iStartDemoTest();
    }

    /**
     * @When I don't have a demo test already in progress
     */
    function iDontHaveDemoTestAlreadyInProgress()
    {
        if (null !== $this->motTestResponse) {
            throw new \LogicException('You already have a demo test in progress');
        }
    }

    /**
     * @Then I can complete a Demo test for vehicle class :vehicleClassCode
     *
     * @param string $vehicleClassCode
     */
    function iCanCompleteDemoTestForVehicleClass($vehicleClassCode)
    {
        $this->vehicleData->create($vehicleClassCode);

        $this->motTestData->createPassedMotTest(
            $this->userData->getCurrentLoggedUser(),
            null,
            $this->vehicleData->create($vehicleClassCode),
            MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING

        );
    }

    /**
     * @Given I pass Mot Test with a Class :testClass Vehicle
     *
     * @param $testClass
     */
    public function IPassMotTestWithAClassVehicle($testClass)
    {
        $this->motTestData->createPassedMotTest(
            $this->userData->getCurrentLoggedUser(),
            $this->siteData->get(),
            $this->vehicleData->create($testClass)
        );
    }

    /**
     * @Given I fail Mot Test with a Class :testClass Vehicle
     *
     * @param $testClass
     */
    public function IFailMotTestWithAClassVehicle($testClass)
    {
        $this->motTestData->createFailedMotTest(
            $this->userData->getCurrentLoggedUser(),
            $this->siteData->get(),
            $this->vehicleData->create($testClass)
        );
    }

    /**
     * @Given /^I start an Mot Test with a Class (.*) Vehicle$/
     *
     * @param $testClass
     */
    public function iStartAnMotTestWithAClassVehicle($testClass)
    {
        $user = $this->userData->getCurrentLoggedUser();
        $vehicle = $this->vehicleData->createWithVehicleClass($user->getAccessToken(), $testClass);

        $mot = $this->motTestData->create($user, $vehicle, $this->siteData->get());

        PHPUnit::assertInstanceOf(MotTestDto::class, $mot);
    }

    /**
     * @When I attempt to start an Mot Test for a class :testClass vehicle
     */
    public function iAttemptToStartAnMotTestForAClassVehicle($testClass)
    {
        $tester = $this->userData->createTesterWithParams([PersonParams::SITE_IDS => [$this->siteData->get()->getId()]]);
        $vehicleId = $this->vehicleContext->createVehicle($tester->getAccessToken(), [VehicleParams::TEST_CLASS => $testClass]);

        $this->motTestResponse = $this->motTest->startNewMotTestWithVehicleId(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId(),
            $vehicleId,
            $this->siteData->get()->getId(),
            $testClass
        );
    }

    /**
     * @When /^the Tester Aborts the Mot Test$/
     */
    public function theTesterAbortsTheMotTest()
    {
        $this->motTestData->abortMotTest($this->motTestData->getAll()->last());
    }

    /**
     * @Then /^the Test will not be Failed as there are no Failures$/
     */
    public function theTestWillNotBeFailedAsThereAreNoFailures()
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_400, $this->motTestData->getLastResponse()->getStatusCode(), 'Incorrect Status Code Returned');
        PHPUnit::assertEquals('The MOT Test does not contain failures and can not be failed', $this->motTestData->getLastResponse()->getBody()->getErrors()[0]['message']);
    }

    /**
     * @Then /^the Test will not be Passed as there are Failures$/
     */
    public function theTestWillNotBePassedAsThereAreFailures()
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_400, $this->motTestData->getLastResponse()->getStatusCode(), 'Incorrect Status Code Returned');
        PHPUnit::assertEquals('The MOT Test contains failures and can not be passed', $this->motTestData->getLastResponse()->getBody()->getErrors()[0]['message']);
    }

    /**
     * @Then /^the Test will not be Aborted as the Test is Complete$/
     */
    public function theTestWillNotBeAbortedAsTheTestIsComplete()
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_403, $this->motTestData->getLastResponse()->getStatusCode(), 'Incorrect Status Code Returned');
        PHPUnit::assertEquals('The MOT Test is incomplete, unable to change status to PASSED', $this->motTestData->getLastResponse()->getBody()->getErrors()[0]['message']);
    }

    /**
     * @Then /^the Test will not Complete as it's In Progress$/
     */
    public function theTestWillNotCompleteAsItSInProgress()
    {
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_400, $this->motTestData->getLastResponse()->getStatusCode(), 'Incorrect Status Code Returned');
        PHPUnit::assertEquals('The MOT Test is incomplete, unable to change status to PASSED', $this->motTestData->getLastResponse()->getBody()->getErrors()[0]['message']);
    }

    /**
     * @Then /^an MOT test number should be allocated$/
     */
    public function anMOTTestNumberShouldBeAllocated()
    {
        $mot = $this->motTestData->getLast();

        PHPUnit::assertNotEmpty($mot->getMotTestNumber(), 'Demo MOT Test number is empty');
    }

    /**
     * @Then /^an MOT test number should not be allocated$/
     */
    public function anMOTTestNumberShouldNotBeAllocated()
    {
        try {
            $body = $this->motTestData->getLastResponse()->getBody();
            PHPUnit::assertThat($body->offsetExists("data"), PHPUnit::isEmpty(), 'MOT Test number returned in response message');
        } catch (\LogicException $e) {
        }
    }

    /**
     * @Given /^I attempt to create a Demo MOT Test$/
     */
    public function iAttemptToCreateADemoMOTTest()
    {
        $user = $this->userData->getCurrentLoggedUser();
        $token = null;
        if ($user !== null) {
            $token = $this->userData->getCurrentLoggedUser()->getAccessToken();
        }

        $vehicle = $this->vehicleData->createByUser($token);
        $this->motTestResponse = $this
            ->demoTest
            ->startMotTest(
                $this->sessionContext->getCurrentAccessTokenOrNull(),
                $vehicle->getId(),
                $this->siteData->get()->getId()
            );
    }

    /**
     * @Given /^I attempt to start an Mot Test with a Class (\d+) Vehicle$/
     *
     * @param $testClass
     */
    public function iAttemptToStartAnMotTestWithAClassVehicle($testClass)
    {
        $vehicle = $this->vehicleData->create($testClass);

        try {
            $this->motTestData->create(
                $this->userData->getCurrentLoggedUser(),
                $vehicle,
                $this->siteData->get()
            );
        } catch (\Exception $e) {

        }

    }

    /**
     * @When /^a logged in Vehicle Examiner aborts the test$/
     */
    public function anAuthenticatedVehicleExaminerAbortsTheTest()
    {
        $user = $this->userData->createVehicleExaminer();

        $this->motTestData->abortMotTestByVE($this->motTestData->getLast(), $user);
    }

    /**
     * @Given /^the Tester cancels the test with a reason of (\d+)$/
     *
     * @param $cancelReasonId
     */
    public function theTesterCancelsTheTestWithAReasonOf($cancelReasonId)
    {
        /** @var MotTestDto $mot */
        $mot = $this->motTestData->getAll()->last();
        $this->motTestData->abandonMotTest($mot, $cancelReasonId);
    }

    /**
     * @Then /^I should receive the MOT test number$/
     */
    public function iShouldReceiveTheMotTestNumber()
    {
        //Get the "In Progress" MOT Test number for the user
        $motTestNumber = $this->motTest->getInProgressTestId(
            $this->sessionContext->getCurrentAccessToken(), $this->sessionContext->getCurrentUserId()
        );

        PHPUnit::assertTrue(is_numeric($motTestNumber), 'MOT Test number is not a number or was not returned.');

        $this->inProgressMotTestNumber = $motTestNumber;
    }

    /**
     * @Given /^the MOT Test Number should be (\d+) digits long$/
     *
     * @param $length
     */
    public function theMOTTestNumberShouldBeDigitsLong($length)
    {
        PHPUnit::assertEquals($length, strlen($this->inProgressMotTestNumber), 'MOT Test number is not 12 digits long.');
    }

    /**
     * @Given there is a :testStatus MOT test
     * @Given there is a :testStatus :testType MOT test
     */
    public function thereIsAMot($testStatus, $testType = MotTestTypeCode::NORMAL_TEST)
    {
        $tester = $this->userData->createTesterWithParams([PersonParams::SITE_IDS => [$this->siteData->get()->getId()]]);
        $vehicle = $this->vehicleData->createByUser($tester->getAccessToken());
        $params = [
            MotTestParams::TYPE => $testType,
            MotTestParams::STATUS => $testStatus,
            MotTestParams::RFR_ID => null,
        ];
        $this->motTestData->createCompletedMotTest($tester, $this->siteData->get(), $vehicle, $params);
    }

    public function createCompletedMotTest($status, $testType, array $params = [])
    {
        $tester = $this->userData->createTesterWithParams([PersonParams::SITE_IDS => [$this->siteData->get()->getId()]]);
        $vehicle = $this->vehicleData->createByUser($tester->getAccessToken());
        $default = [
            "vehicleId" => $vehicle->getId(),
            "vehicleClass" => $vehicle->getVehicleClass()->getCode(),
            "siteId" => $this->siteData->get()->getId(),
            "token" => $tester->getAccessToken(),
            "rfrId" => null
        ];

        $params = array_replace($default, $params);

        $vehicleId = $params["vehicleId"];
        $vehicleClass = $params["vehicleClass"];
        $siteId = $params["siteId"];
        $token = $params["token"];
        $rfrId = $params["rfrId"];

        if ($token === null) {
            $this->sessionContext->iAmLoggedInAsATester();
            $token = $this->sessionContext->getCurrentAccessToken();
        }

        if ($testType === 'contingency') {
            $response = $this->startStartAContingencyMOTTest($token, $vehicleClass, $siteId);
            $lastMotTestNumber = $response->getBody()->getData()[MotTestParams::MOT_TEST_NUMBER];
            $mot = $this->motTest;
        } elseif ($testType === 'demo') {
            $mot = $this->getMotTest($testType);
            $this->motTestResponse = $mot->startMotTest($token, $vehicleId, $vehicleClass);
            $lastMotTestNumber = $mot->getLastMotTestNumber();
        } else {
            $mot = $this->getMotTest($testType);
            $this->motTestResponse = $mot->startMotTest($token, $vehicleId, $vehicleClass, ["vehicleTestingStationId" => $siteId]);
            $lastMotTestNumber = $mot->getLastMotTestNumber();
        }

        // Set the bits so that we can pass or fail the test
        $this->odometerReading->addMeterReading($token, $lastMotTestNumber, 658, MeterReadingParams::MI);

        if ($rfrId === null) {
            $rfrId = ($vehicleClass < VehicleClassCode::CLASS_3)
                ? ReasonForRejectionGroupA::RFR_BRAKE_HANDLEBAR_LEVER
                : ReasonForRejectionGroupB::RFR_BODY_STRUCTURE_CONDITION;
        }

        if ($vehicleClass < VehicleClassCode::CLASS_3) {
            $this->brakeTestResult->addBrakeTestDecelerometerClass1To2($token, $lastMotTestNumber);
        } else {
            $this->brakeTestResult->addBrakeTestDecelerometerClass3To7($token, $lastMotTestNumber);
        }

        switch($status) {
            case 'passed':
                $mot->passed($token, $lastMotTestNumber);
                break;
            case 'failed':
                $this->reasonForRejection->addFailure($token, $lastMotTestNumber, $rfrId);
                $mot->failed($token, $lastMotTestNumber);
                break;
            case 'prs':
                $this->reasonForRejection->addPrs($token, $lastMotTestNumber, $rfrId);
                $mot->passed($token, $this->getMotTestNumber());
                break;
            case 'abandoned':
                $mot->abandon($token, $lastMotTestNumber, 23);
                break;
            case 'aborted':
                $mot->abandon($token, $lastMotTestNumber, 5);
                break;
            default:
                throw new \Exception("Unrecognised status '{$status}'");
                break;
        }

        return $mot;
    }

    /**
     * @Given I have passed an MOT test
     */
    public function iHavePassedAnMotTest()
    {
        $this->iStartMotTest();
        $this->odometerReadingContext->theTesterAddsAnOdometerReadingOfMiles();
        $this->brakeTestResultContext->theTesterAddsAClass3to7PlateBrakeTest();
        $this->theTesterPassesTheMotTest();
    }

    /**
     * @Given there is a Mot test with :testType type in progress
     */
    public function thereIsAMotTestWithTypeInProgress($testType)
    {
        $this->motTestData->create(
            $this->userData->createTester("Mike Tyson"),
            $this->vehicleData->create(),
            $this->siteData->get(),
            $testType
        );
    }

    public function anMotHasBeenPassed()
    {
        $this->sessionContext->iAmLoggedInAsATester();
        $this->iHavePassedAnMotTest();
    }

    /**
     * @Then I can view the :test MOT summary
     */
    public function iCanViewTheMotSummary($test)
    {
        $mot = $this->getMotTest($test);

        $motTestData = $mot->getMotData($this->sessionContext->getCurrentAccessToken(), $mot->getLastMotTestNumber());
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $motTestData->getStatusCode());
        PHPUnit::assertNotEquals(MotTestStatusName::ACTIVE, $motTestData->getBody()->getData()[MotTestParams::STATUS]);
    }

    /**
     * @When I abort the Mot Test
     */
    public function theUserAbortsTheMotTest()
    {
        $mot = $this->motTestData->getLast();
        try {
            $this->motTestData->abortMotTestByUser($mot, $this->userData->getCurrentLoggedUser());
        } catch (\Exception $e) {
            $response = $this->motTestData->getLastResponse();
            $x = 1;
        }


    }

    /**
     * @When /^I start a Contingency MOT test$/
     */
    public function iStartAContingencyMOTTest()
    {
        $user = $this->userData->getCurrentLoggedUser();
        $this->motTestData->create(
            $user,
            $this->vehicleData->createByUser($user->getAccessToken()),
            $this->siteData->get(),
            MotTestData::TEST_TYPE_CONTINGENCY
        );
    }

    public function startStartAContingencyMOTTest($token, $testClass, $siteId = null)
    {
        if ($siteId === null) {
            $siteId = $this->siteData->get()->getId();
        }

        $vehicleId = $this->vehicleContext->createVehicle($token, [VehicleParams::TEST_CLASS => $testClass]);
        $this->contingencyTestContext->createContingencyCode(ContingencyData::CONTINGENCY_CODE, 'SO', null, $token, $siteId);

        $emergencyLogId = $this->contingencyTestContext->getEmergencyLogId();

        $this->motTestResponse = $this->contingencyTest->startContingencyTest(
            $token,
            $emergencyLogId,
            $vehicleId,
            $testClass,
            $siteId
        );

        return $this->motTestResponse;
    }

    /**
     * @When /^I record a Contingency Test with (.*) at ([0-9]{2}:[0-9]{2}:[0-9]{2}|now)$/
     * @param $date
     * @param $time
     */
    public function iStartAContingencyMOTTestOnDateAtTime($date, $time)
    {
        $dateTime = new DateTime();

        if ($date != 'today') {
            $dateTime->modify($date);
        }

        if ($time != 'now') {
            $timeParts = explode(':', $time);
            $dateTime->setTime($timeParts[0], $timeParts[1], $timeParts[2]);
        }

        $this->contingencyMotTestData->create(
            $this->userData->getCurrentLoggedUser(),
            $this->vehicleData->createByUser($this->userData->getCurrentLoggedUser()->getAccessToken()),
            $this->siteData->get(),
            ["dateTime" => $dateTime]
        );
    }

    /**
     * @Given /^the Contingency Test is Logged$/
     */
    public function theContingencyTestIsLogged()
    {
        /** @var MotTestDto $motTest */
        $motTest = $this->motTestData->getAll()->last();

        $contingencyDto = $this->contingencyData->getBySiteId($this->siteData->get()->getId());
        PHPUnit::assertEquals($contingencyDto->getContingencyCode(), $motTest->getEmergencyLog()[ContingencyDataParams::NUMBER], 'Contingency Code not returned.');
        PHPUnit::assertEquals($this->contingencyData->getEmergencyLogId(), $motTest->getEmergencyLog()[ContingencyDataParams::ID], 'Emergency Log Id not returned.');
    }

    /**
     * @Then I am unable to start a new Demo MOT test
     */
    public function iAmUnableToStartANewDemoMotTest()
    {
        $userId = $this->userData->getCurrentLoggedUser()->getUserId();
        /** @var MotTestDto $mot */
        $mot = $this->motTestData->getAll()->filter(function (MotTestDto $dto) use ($userId) {
            return $dto->getTester()->getId() === $userId;
        })->first();

        $motDetails = $this->motTestData->fetchMotTestData($this->userData->getCurrentLoggedUser(), $mot->getMotTestNumber());

        PHPUnit::assertEquals(MotTestStatusName::ACTIVE, $motDetails->getStatus());
    }

    /**
     * @return string
     */
    public function getMotTestNumber()
    {
        if (!$this->motTestResponse) {
            throw new \BadMethodCallException('There is no MOT test in progress');
        }

        return $this->motTestResponse->getBody()->getData()[MotTestParams::MOT_TEST_NUMBER];
    }

    /**
     * @Then /^the recorded IP is "([^"]*)"$/
     */
    public function theRecordedIPIs($clientIp)
    {
        $actualClientIp = $this->statusData->getBody()->getData()['clientIp'];

        PHPUnit::assertEquals($clientIp, $actualClientIp, 'Recorded client IP incorrect');
    }

    /**
     * @When /^the Tester Passes the Mot Test from IP "([^"]*)"$/
     *
     * @param string $clientIp
     */
    public function theTesterPassesTheMotTestFromIp($clientIp)
    {
        $this->statusData = $this->motTest->passedWithIp(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getMotTestNumber(),
            $clientIp
        );
    }

    /**
     * @Given /^I create (.*) mot tests$/
     * @Given /^I create an mot test$/
     * @Given /^I have created (.*) mot tests$/
     * @Given I have created :number mot tests for :siteName site
     *
     * @param $number
     */
    public function ICreateMotTests($number = 1, $siteName = SiteData::DEFAULT_NAME)
    {
        $motTestParams = [];
        if(!empty($siteName)) {
            $site = $this->siteData->get($siteName);
            $motTestParams[MotTestParams::VEHICLE_TESTING_STATION_ID] = $site->getId();
        }

        $user = $this->userData->getCurrentLoggedUser();
        for ($i=0; $i < $number; $i++) {
            $this->motTestData->createAbortedMotTest($user, $this->siteData->get($siteName), $this->vehicleData->create());
        }
    }

    /**
     * @Given /^I attempt to create a MOT Test on a vehicle without a manufactured date and first used date$/
     *
     */
    public function IAttemptToCreateAnMOTTestOnAVehicleWithoutAManufacturerAndFirstUsedDate()
    {
        $vehicleData =  [
            VehicleParams::DATE_OF_FIRST_USE => null,
            VehicleParams::MANUFACTURE_DATE => null,
            VehicleParams::VIN => 'WF0BXXGAJB1R41234',
            VehicleParams::REGISTRATION_NUMBER => 'F50GGP'
        ];

        $this->vehicleId = $this->vehicleContext->createVehicle($this->sessionContext->getCurrentAccessToken(), $vehicleData);

        $this->motTestResponse = $this->motTest->startNewMotTestWithVehicleId(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId(),
            $this->vehicleId,
            $this->siteData->get()->getId()
        );
    }

    /**
     * @Then /^MOT test should be created successfully$/
     *
     */
    public function MOTTestShouldBeCreatedSuccessfully()
    {
        PHPUnit::assertNotNull($this->motTestResponse);
    }

    /**
     * @param string $test
     * @return DemoTest|MotTest
     * @throws Exception
     */
    private function getMotTest($test)
    {
        switch ($test) {
            case 'demo':
                $mot = $this->demoTest;
                break;
            case 'normal':
                $mot = $this->motTest;
                break;
            default:
                throw new \Exception("Unrecognised test type '{$test}'");
                break;
        }
        return $mot;
    }

    /**
     * @Given :number MOT tests have been created by different testers with the same prefix
     */
    public function motTestsHaveBeenCreatedByDifferentTestersWithTheSamePrefix($number)
    {
        $dataGeneratorHelper = $this->testSupportHelper->getDataGeneratorHelper();
        $baseUsername = $dataGeneratorHelper->generateRandomString(self::USERNAME_PREFIX_LENGTH);
        $suffix = $dataGeneratorHelper->generateRandomString(2);

        while ($number) {
            $username = $baseUsername . str_repeat($suffix, $number);

            $user = $this->userData->createTester($username);
            $vehicle = $this->vehicleData->create();
            $this->motTestData->createPassedMotTest($user, $this->siteData->get(), $vehicle);
            $number--;
        }
    }

    /**
     * @Given I Create a new vehicle
     */
    public function iCreateANewVehicle()
    {
        $this->vehicleData->createByUser($this->userData->getCurrentLoggedUser()->getAccessToken());
    }


    /**
     * @Given :number passed MOT tests have been created for the same vehicle
     */
    public function passedMotTestsHaveBeenCreatedForTheSameVehicle($number)
    {
        while ($number) {
            $this->createPassedMotTest(
                $this->userData->getCurrentLoggedUser()->getUserId(),
                $this->userData->getCurrentLoggedUser()->getAccessToken(),
                $this->vehicleData->getLast()->getId(),
                $this->getUniqueOdometer()
            );
            $number--;
        }
    }

    /**
     * @Given :number failed MOT tests have been created for the same vehicle
     */
    public function failedMotTestsHaveBeenCreatedForTheSameVehicle($number)
    {
        while ($number) {
            $this->vehicleHasMotTestFailed($this->vehicleData->getLast()->getId(), [], $this->getUniqueOdometer());
            $number--;
        }
    }

    /**
     * @Given :number passed MOT tests have been migrated for the same vehicle
     */
    public function passedMotTestsHaveBeenMigratedForTheSameVehicle($number)
    {
        //We emulate case for migrated mot_tests: mot_tests are created but jasper_documents do not exist
        $this->passedMotTestsHaveBeenCreatedForTheSameVehicle($number);
        $this->certificateContext->removeJasperDocumentsForMotTests();
    }

    public function createPassedMotTest($userId, $token, $vehicleId = null, $odometerValue = null)
    {
        $mot = $this->startMotTest($userId, $token, [], $vehicleId);
        $this->brakeTestResult->addBrakeTestDecelerometerClass3To7($token, $mot->getMotTestNumber());
        $this->odometerReading->addMeterReading($token, $mot->getMotTestNumber(), is_null($odometerValue) ? date('Gis') : $odometerValue, MeterReadingParams::KM);

        return $this->motTestData->passMotTest($mot);
    }

    /**
     * @Given a MOT test for vehicle with the following data exists:
     */
    public function aMotTestForVehicleWithTheFollowingDataExists(TableNode $table)
    {
        $hash = $table->getColumnsHash();

        if (count($hash) !== 1) {
            throw new \InvalidArgumentException(sprintf('Expected a single vehicle record but got: %d', count($hash)));
        }

        $row = $hash[0];

        $vehicleData = [
            VehicleParams::MAKE => $row['make_code'],
            VehicleParams::MAKE_OTHER => $row['make_other'],
            VehicleParams::MODEL => null,
            VehicleParams::MODEL_OTHER => $row['model_other']
        ];

        $tester = $this->userData->createTesterAssignedWitSite($this->siteData->get()->getId(), 'Vehicle Constructor');
        $this->vehicleId = $this->vehicleData->createWithParams($tester->getAccessToken(), $vehicleData)->getId();

        $this->createPassedMotTest(
            $tester->getUserId(),
            $tester->getAccessToken(),
            $this->vehicleId
        );
    }

    /**
     * @Given vehicle has a Re-Test test started
     */
    public function vehicleHasMotTestReTestStarted()
    {
        $this->vehicleHasMotTestFailed();

        $this->startMotTest($this->sessionContext->getCurrentUserId(),
            $this->sessionContext->getCurrentAccessToken(),
            ['motTestType' => MotTestTypeCode::RE_TEST],
            !empty($this->vehicleId) ? $this->vehicleId : null
        );
    }

    protected function vehicleHasMotTestStarted($testType)
    {
        $this->startMotTest($this->sessionContext->getCurrentUserId(),
            $this->sessionContext->getCurrentAccessToken(),
            ['motTestType' => $testType],
            !empty($this->vehicleId) ? $this->vehicleId : null
        );
        $this->odometerReading->addMeterReading($this->sessionContext->getCurrentAccessToken(), $this->motTest->getLastMotTestNumber(), 111, MeterReadingParams::MI);
        $this->brakeTestResult->addBrakeTestDecelerometerClass3To7($this->sessionContext->getCurrentAccessToken(), $this->motTest->getLastMotTestNumber());
        $this->theTesterPassesTheMotTest();
    }


    public function vehicleHasMotTestFailed($vehicleId = null, $motTestParams = [], $odometerValue = 658)
    {
        $user = $this->userData->getCurrentLoggedUser();
        $this->startMotTest(
            $user->getUserId(),
            $user->getAccessToken(),
            $motTestParams,
            $vehicleId
        );
        $this->odometerReading->addMeterReading($user->getAccessToken(), $this->motTestData->getLast()->getMotTestNumber(), $odometerValue, MeterReadingParams::MI);
        $this->brakeTestResult->addBrakeTestDecelerometerClass3To7($user->getAccessToken(), $this->motTestData->getLast()->getMotTestNumber());
        $this->theTesterAddsAReasonForRejection();
        $this->theTesterFailsTheMotTest();
    }
    /**
     * @Given vehicle has a Normal Test test started
     */
    public function vehicleHasANormalTestTestStarted()
    {
        $this->vehicleHasMotTestStarted(MotTestTypeCode::NORMAL_TEST);
    }

    /**
     * @Given vehicle has a Partial Retest Left VTS test started
     */
    public function vehicleHasAPartialRetestLeftVtsTestStarted()
    {
        $this->vehicleHasMotTestStarted(MotTestTypeCode::PARTIAL_RETEST_LEFT_VTS);
    }

    /**
     * @Given vehicle has a Partial Retest Repaired at VTS test started
     */
    public function vehicleHasAPartialRetestRepairedAtVtsTestStarted()
    {
        $this->vehicleHasMotTestStarted(MotTestTypeCode::PARTIAL_RETEST_REPAIRED_AT_VTS);
    }

    /**
     * @Given vehicle has a Targeted Reinspection test started
     */
    public function vehicleHasATargetedReinspectionTestStarted()
    {
        $this->vehicleHasMotTestFailed();

        $this->startMotTest($this->userData->getCurrentLoggedUser()->getUserId(),
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            ['motTestType' => MotTestTypeCode::TARGETED_REINSPECTION],
            !empty($this->vehicleId) ? $this->vehicleId : null
        );
    }

    /**
     * @Given vehicle has a MOT Compliance Survey test started
     */
    public function vehicleHasAMotComplianceSurveyTestStarted()
    {
        $this->vehicleHasMotTestStarted(MotTestTypeCode::MOT_COMPLIANCE_SURVEY);
    }

    /**
     * @Given vehicle has a Inverted Appeal test started
     */
    public function vehicleHasAInvertedAppealTestStarted()
    {
        $this->vehicleHasMotTestStarted(MotTestTypeCode::INVERTED_APPEAL);
    }

    /**
     * @Given vehicle has a Statutory Appeal test started
     */
    public function vehicleHasAStatutoryAppealTestStarted()
    {
        $this->vehicleHasMotTestStarted(MotTestTypeCode::STATUTORY_APPEAL);
    }

    /**
     * @Given vehicle has a Other test started
     */
    public function vehicleHasAOtherTestStarted()
    {
        $this->vehicleHasMotTestStarted(MotTestTypeCode::OTHER);
    }

    /**
     * @Given vehicle has a Demonstration Test following training test started
     */
    public function vehicleHasADemonstrationTestFollowingTrainingTestStarted()
    {
        $this->startMotTest($this->sessionContext->getCurrentUserId(),
            $this->sessionContext->getCurrentAccessToken(),
            [
                'motTestType'      => MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
                'vehicleStationId' => null
            ]
        );
    }

    /**
     * @Given vehicle has a Routine Demonstration Test test started
     */
    public function vehicleHasARoutineDemonstrationTestTestStarted()
    {
        $this->vehicleHasMotTestStarted(MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING);
    }

    public function vehicleHasAbortedTest()
    {
        $this->startMotTest($this->sessionContext->getCurrentUserId(),
            $this->sessionContext->getCurrentAccessToken(),
            ['motTestType' => MotTestTypeCode::NORMAL_TEST],
            !empty($this->vehicleId) ? $this->vehicleId : null
        );

        $this->theTesterAbortsTheMotTest();
    }

    /**
     * @Given vehicle has a Non-Mot Test test started
     */
    public function vehicleHasANonMotTestTestStarted()
    {
        $this->vehicleHasMotTestStarted(MotTestTypeCode::NON_MOT_TEST);
    }

    /**
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function getRawMotTestData()
    {
        return $this->motTestResponse;
    }

    public function getMotTestNumbers()
    {
        return $this->motTestNumbers;
    }

    public function getMotTests()
    {
        if (empty($this->motTests)) {
            $this->motTests = [];

            foreach ($this->getMotTestNumbers() as $motTestNumber) {
                $motTest = $this->motTest->getMotData($this->sessionContext->getCurrentAccessToken(), $motTestNumber)->getBody()->getData();
                $this->motTests[] = $motTest;
            }
        }

        usort($this->motTests, function($a, $b) {
            return $a[MotTestParams::ID] < $b[MotTestParams::ID];
        });

        return $this->motTests;
    }

    public function setMotTests(array $motTests)
    {
        $this->motTests = $motTests;
    }

    public function refreshMotTests()
    {
        $this->motTests=[];
        return $this->getMotTests();
    }

    /**
     * Alternative to sleep: using sleep starves behat process on jenkins (needed to prevent tests being issued in the same second)
     * @param int $howLong
     */
    private function behatWait($howLong = 1)
    {
        $then = microtime(true);

        while($then + $howLong > microtime(true)) {
            echo '.';
        }
    }

    /**
     * @Given /^I can add PRS to test$/
     */
    public function iCanAddPRSToTest()
    {
        /** @var MotTestDto $mot */
        $mot = $this->motTestData->getAll()->last();

        $response = $this->reasonForRejection->addPrs(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $mot->getMotTestNumber()
        );

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Given /^I can add a Failure to test$/
     */
    public function iCanAddAFailureToTest()
    {
        /** @var MotTestDto $mot */
        $mot = $this->motTestData->getAll()->last();

        $response = $this->reasonForRejection->addFailure(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $mot->getMotTestNumber()
        );

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Given /^I can edit previously added Rfr$/
     */
    public function iCanEditPreviouslyAddedRfr()
    {
        /** @var MotTestDto $mot */
        $mot = $this->motTestData->getAll()->last();

        $response = $this->reasonForRejection->editRFR(
            $this->userData->getCurrentLoggedUser()->getAccessToken(),
            $mot->getMotTestNumber(),
            $this->history->getLastResponse()->getBody()->getData()
        );

        PHPUnit::assertSame(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Then I can not start an Mot Test for Vehicle with class :vehilceClass
     */
    public function iCanNotStartAnMotTestForVehicleWithClass($vehicleClass)
    {
        $vehicle = $this->vehicleData->create($vehicleClass);

        try {
            $response = null;
            $this->motTestData->create(
                $this->userData->getCurrentLoggedUser(),
                $vehicle,
                $this->siteData->get()

            );
        } catch (\Exception $e) {
            $response = $this->motTestData->getLastResponse();
        }


        PHPUnit::assertSame(HttpResponse::STATUS_CODE_403, $response->getStatusCode());

        $expectedError = sprintf("Your Site is not authorised to test class %s vehicles", $vehicleClass);
        $apiErrors = $response->getBody()->offsetGet("errors")->toArray();
        $error = array_shift($apiErrors)["message"];

        PHPUnit::assertSame($expectedError, $error);
    }

    /**
     * @Then /^the controlOne and controlTwo status should be (.*) (.*)$/
     */
    public function theControlOneAndControlTwoStatusShouldBe($expectedControl1Pass, $expectedControl2Pass)
    {
        /** @var MotTestDto $mot */
        $mot = $this->motTestData->getAll()->last();

        $actualControl1Pass = $mot->getBrakeTestResult()[BrakeTestResultParams::CONTROL_1_EFFICIENCY_PASS];
        $actualControl2Pass = $mot->getBrakeTestResult()[BrakeTestResultParams::CONTROL_2_EFFICIENCY_PASS];

        PHPUnit::assertEquals($expectedControl1Pass, $actualControl1Pass);
        PHPUnit::assertEquals($expectedControl2Pass, $actualControl2Pass);
    }

    /**
     * @Then /^the Mot test status should be (.*)$/
     */
    public function theMotTestStatusShouldBe($expectedResult)
    {
        $mot = $this->motTestData->getAll()->last();
        PHPUnit::assertEquals($expectedResult, $mot->getStatus());
    }

    /**
     * @Given there is a test performed at the VTS when it's linked to :ae AE :time time
     */
    public function iPerformTestOnTheVtsWhenItsLinkedToAe($aeName, $time)
    {
        $ae = $this->authorisedExaminerData->create($aeName);
        $site = $this->siteData->createUnassociatedSite([SiteParams::NAME => "some site"]);
        $tester = $this->userData->createTesterAssignedWitSite($site->getId(), "John Kowalsky");
        $vehicle = $this->vehicleData->createByUser($tester->getAccessToken());

        $this->authorisedExaminerData->linkAuthorisedExaminerWithSite($ae, $site);

        $this->motTestData->createPassedMotTest($tester, $site, $vehicle);

        //When we assign VTS back to first AE, we don't want to unlink them after test
        if($time != 'second') {
            $this->authorisedExaminerData->unlinkSiteFromAuthorisedExaminer($ae, $site);
        }
    }

    public function getMotTestIdFromNumber($motTestNumber)
    {
        return $this->motTest->getMotData(
            $this->sessionContext->getCurrentAccessToken(),
            $motTestNumber
        )->getBody()->getData()[MotTestParams::ID];
    }

    /**
     * @return Response
     */
    public function getMotStatusData()
    {
        return $this->statusData;
    }

    private function getUniqueOdometer()
    {
        $this->behatWait();
        return date('Gis');
    }
}
