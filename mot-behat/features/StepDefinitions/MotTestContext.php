<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Api\ContingencyTest;
use Dvsa\Mot\Behat\Support\Api\DemoTest;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\OdometerReading;
use Dvsa\Mot\Behat\Support\Api\ReasonForRejection;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Vehicle;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use Dvsa\Mot\Behat\Support\Response;
use DvsaCommon\Enum\MotTestTypeCode;
use Dvsa\Mot\Behat\Support\History;
use PHPUnit_Framework_Assert as PHPUnit;

class MotTestContext implements Context, SnippetAcceptingContext
{
    const SITE_NUMBER = 'V1234';
    const USERNAME_PREFIX_LENGTH = 20;

    /**
     * @var Response
     */
    private $motTestData;

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
     * @var MotTestTypeCode
     */
    private $MotTestTypeCode;

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

    public function __construct(
        BrakeTestResult $brakeTestResult,
        MotTest $motTest,
        DemoTest $demoTest,
        ContingencyTest $contingencyTest,
        OdometerReading $odometerReading,
        ReasonForRejection $reasonForRejection,
        Session $session,
        TestSupportHelper $testSupportHelper,
        Vehicle $vehicle,
        History $history
    ) {
        $this->brakeTestResult = $brakeTestResult;
        $this->motTest = $motTest;
        $this->demoTest = $demoTest;
        $this->contingencyTest = $contingencyTest;
        $this->odometerReading = $odometerReading;
        $this->reasonForRejection = $reasonForRejection;
        $this->session = $session;
        $this->testSupportHelper = $testSupportHelper;
        $this->vehicle = $vehicle;
        $this->history = $history;
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
    }

    /**
     * @Then /^the MOT Test Status is "([^"]*)"$/
     * @Then /^the MOT Test Status should be "([^"]*)"$/
     *
     * @param $status
     */
    public function theMOTTestStatusIs($status)
    {
        $actualResponse = $this->statusData->getBody()['data']['status'];

        PHPUnit::assertEquals($status, $actualResponse, 'MOT Test Status is incorrect');
    }

    /**
     * @Given a logged in Tester, starts an MOT Test
     * @Given I start an MOT test as a Tester
     */
    public function iStartMotTestAsTester()
    {
        $this->sessionContext->iAmLoggedInAsATester();
        $this->startMotTest($this->sessionContext->getCurrentUserId(), $this->sessionContext->getCurrentAccessToken());
    }

    public function startMotTest($userId, $token, $motTestParams = [], $vehicleId = null)
    {
        $testClass = 4;

        if (is_null($vehicleId)) {
            $this->vehicleId  = $this->vehicleContext->createVehicle(['testClass' => $testClass]);
        } else {
            $this->vehicleId = $vehicleId;
        }

        $this->motTestData = $this->motTest->startNewMotTestWithVehicleId(
            $token,
            $userId,
            $this->vehicleId,
            $testClass,
            $motTestParams
        );
    }

    /**
     * @When I start a Demo MOT Test
     */
    public function iStartDemoTest()
    {
        $vehicleId = $this->vehicleContext->createVehicle();

        $this->motTestData = $this->demoTest->startMotTest(
            $this->sessionContext->getCurrentAccessToken(),
            $vehicleId
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
        $rfrId = $this->vehicleContext->getCurrentVehicleClass() < 3 ? 356 : 8455;
        $response = $this->reasonForRejection->addFailure($this->sessionContext->getCurrentAccessToken(), $this->getMotTestNumber(), $rfrId);
        PHPUnit::assertSame(200, $response->getStatusCode());
    }

    /**
     * @Then /^I can search for Rfr$/
     */
    public function iCanSearchForRfr()
    {
        $response = $this->reasonForRejection->search(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getMotTestNumber(),
            "brake", 0, 2
        );
        PHPUnit::assertSame(200, $response->getStatusCode());
    }

    /**
     * @Then /^I can list child test items selector$/
     */
    public function iCanListChildTestItemSelector()
    {
        $response = $this->reasonForRejection->listTestItemSelectors(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getMotTestNumber()
        );
        PHPUnit::assertSame(200, $response->getStatusCode());
    }

    /**
     * @When /^the Tester Fails the Mot Test$/
     */
    public function theTesterFailsTheMotTest()
    {
        $this->statusData = $this->motTest->failed(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getMotTestNumber()
        );
    }

    /**
     * @When the Tester Passes the Mot Test
     */
    public function theTesterPassesTheMotTest()
    {
        $this->statusData = $this->motTest->passed(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getMotTestNumber()
        );
    }

    /**
     * @When /^I have an MOT Test In Progress$/
     */
    public function iHaveAnMOTTestInProgress()
    {
        $this->iStartAnMotTestWithAClassVehicle(4);
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
        if (null !== $this->motTestData) {
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
        $vehicleId = $this->vehicleContext->createVehicle(['testClass' => $vehicleClassCode]);

        $this->motTestData = $this->demoTest->startMotTest(
            $this->sessionContext->getCurrentAccessTokenOrNull(), $vehicleId, $vehicleClassCode
        );
        $this->demoTest->passed(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getMotTestNumber()
        );
    }

    /**
     * @Given /^I start an Mot Test with a Class (.*) Vehicle$/
     *
     * @param $testClass
     */
    public function iStartAnMotTestWithAClassVehicle($testClass)
    {
        $vehicleId = $this->vehicleContext->createVehicle(['testClass' => $testClass]);

        $this->motTestData = $this->motTest->startNewMotTestWithVehicleId(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId(),
            $vehicleId,
            $testClass
        );

        PHPUnit::assertSame(200, $this->motTestData->getStatusCode());
    }

    /**
     * @When I attempt to start an Mot Test for a class :testClass vehicle
     */
    public function iAttemptToStartAnMotTestForAClassVehicle($testClass)
    {
        $vehicleId = $this->vehicleContext->createVehicle(['testClass' => $testClass]);

        $this->motTestData = $this->motTest->startNewMotTestWithVehicleId(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId(),
            $vehicleId,
            $testClass
        );
    }

    /**
     * @When /^the Tester Aborts the Mot Test$/
     */
    public function theTesterAbortsTheMotTest()
    {
        $currentMotTestNumber = $this->motTest->getInProgressTestId($this->sessionContext->getCurrentAccessToken(), $this->sessionContext->getCurrentUserId());
        $this->statusData = $this->motTest->abort($this->sessionContext->getCurrentAccessToken(), $currentMotTestNumber);
    }

    /**
     * @Then /^the Test will not be Failed as there are no Failures$/
     */
    public function theTestWillNotBeFailedAsThereAreNoFailures()
    {
        PHPUnit::assertEquals(400, $this->statusData->getStatusCode(), 'Incorrect Status Code Returned');
        PHPUnit::assertEquals('The MOT Test does not contain failures and can not be failed', $this->statusData->getBody()['errors'][0]['message']);
    }

    /**
     * @Then /^the Test will not be Passed as there are Failures$/
     */
    public function theTestWillNotBePassedAsThereAreFailures()
    {
        PHPUnit::assertEquals(400, $this->statusData->getStatusCode(), 'Incorrect Status Code Returned');
        PHPUnit::assertEquals('The MOT Test contains failures and can not be passed', $this->statusData->getBody()['errors'][0]['message']);
    }

    /**
     * @Then /^the Test will not be Aborted as the Test is Complete$/
     */
    public function theTestWillNotBeAbortedAsTheTestIsComplete()
    {
        PHPUnit::assertEquals(403, $this->statusData->getStatusCode(), 'Incorrect Status Code Returned');
        PHPUnit::assertEquals('The MOT Test is incomplete, unable to change status to PASSED', $this->statusData->getBody()['errors'][0]['message']);
    }

    /**
     * @Then /^the Test will not Complete as it's In Progress$/
     */
    public function theTestWillNotCompleteAsItSInProgress()
    {
        PHPUnit::assertEquals(400, $this->statusData->getStatusCode(), 'Incorrect Status Code Returned');
        PHPUnit::assertEquals('The MOT Test is incomplete, unable to change status to PASSED', $this->statusData->getBody()['errors'][0]['message']);
    }

    /**
     * @Given /^the status of the test is (.*)$/
     */
    public function iHaveAMOTTest($status)
    {
        switch (strtolower($status)) {
            case 'passed':
                $this->statusData = $this->motTest->passed(
                    $this->sessionContext->getCurrentAccessToken(),
                    $this->getMotTestNumber()
                );
                break;
            case 'failed':
                $rfrId = ($this->vehicleContext->getCurrentVehicleClass() < 3) ? 356 : 8455;
                $this->reasonForRejection->addFailure($this->sessionContext->getCurrentAccessToken(), $this->getMotTestNumber(), $rfrId);
                $this->statusData = $this->motTest->failed($this->sessionContext->getCurrentAccessToken(), $this->getMotTestNumber());
                break;
            case 'prs':
                $rfrId = ($this->vehicleContext->getCurrentVehicleClass() < 3) ? 356 : 8455;
                $this->reasonForRejection->addPrs($this->sessionContext->getCurrentAccessToken(), $this->getMotTestNumber(), $rfrId);
                $this->statusData = $this->motTest->passed($this->sessionContext->getCurrentAccessToken(), $this->getMotTestNumber());
                break;
        }
    }

    /**
     * @Then /^an MOT test number should be allocated$/
     */
    public function anMOTTestNumberShouldBeAllocated()
    {
        $motTestNumber = $this->getMotTestNumber();

        PHPUnit::assertNotEmpty($motTestNumber, 'Demo MOT Test number is empty');
        PHPUnit::assertTrue(is_numeric($motTestNumber), 'Demo MOT Test number is not numeric.');
    }

    /**
     * @Then /^an MOT test number should not be allocated$/
     */
    public function anMOTTestNumberShouldNotBeAllocated()
    {
        try {
            $motTestNumber = $this->getMotTestNumber();
            PHPUnit::assertThat($motTestNumber, PHPUnit::isEmpty(), 'MOT Test number returned in response message');
        } catch (\LogicException $e) {
        }
    }

    /**
     * @Given /^I attempt to create a Demo MOT Test$/
     */
    public function iAttemptToCreateADemoMOTTest()
    {
        $this->motTestData = $this->demoTest->startMotTest($this->sessionContext->getCurrentAccessTokenOrNull());
    }

    /**
     * @Given /^I attempt to start an Mot Test with a Class (\d+) Vehicle$/
     *
     * @param $testClass
     */
    public function iAttemptToStartAnMotTestWithAClassVehicle($testClass)
    {
        $this->motTestData = $this->motTest->startNewMotTestWithVehicleId(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId(),
            $this->vehicleContext->getCurrentVehicleId(),
            $testClass
        );
    }

    /**
     * @When /^a logged in Vehicle Examiner aborts the test$/
     */
    public function anAuthenticatedVehicleExaminerAbortsTheTest()
    {
        $user = $this->session->startSession(
            Authentication::LOGIN_VEHICLE_EXAMINER_USER, Authentication::PASSWORD_DEFAULT
        );

        $this->statusData = $this->motTest->abortTestByVE($user->getAccessToken(), $this->getMotTestNumber());
    }

    /**
     * @Given /^the Tester cancels the test with a reason of (\d+)$/
     *
     * @param $cancelReasonId
     */
    public function theTesterCancelsTheTestWithAReasonOf($cancelReasonId)
    {
        $this->statusData = $this->motTest->abandon(
            $this->sessionContext->getCurrentAccessToken(), $this->getMotTestNumber(), $cancelReasonId
        );
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
     * @Given there is a :status MOT test
     * @Given there is a :status :test MOT test
     */
    public function thereIsAMot($status, $test = 'normal')
    {
        $this->sessionContext->iAmLoggedInAsATester();

        $mot = $this->getMotTest($test);
        $this->motTestData = $mot->startMotTest($this->sessionContext->getCurrentAccessToken());

        // Set the bits so that we can pass or fail the test
        $this->odometerReading->addMeterReading($this->sessionContext->getCurrentAccessToken(), $mot->getLastMotTestNumber(), 658, 'mi');
        $this->brakeTestResult->addBrakeTestDecelerometerClass3To7($this->sessionContext->getCurrentAccessToken(), $mot->getLastMotTestNumber());

        switch($status) {
            case 'passed':
                $mot->passed($this->sessionContext->getCurrentAccessToken(), $mot->getLastMotTestNumber());
                break;
            case 'failed':
                $this->reasonForRejection->addFailure($this->sessionContext->getCurrentAccessToken(), $mot->getLastMotTestNumber());
                $mot->failed($this->sessionContext->getCurrentAccessToken(), $mot->getLastMotTestNumber());
                break;
            default:
                throw new \Exception("Unrecognised status '{$status}'");
                break;
        }
    }

    /**
     * @Then I can view the :test MOT summary
     */
    public function iCanViewTheMotSummary($test)
    {
        $mot = $this->getMotTest($test);

        $motTestData = $mot->getMotData($this->sessionContext->getCurrentAccessToken(), $mot->getLastMotTestNumber());
        PHPUnit::assertEquals(200, $motTestData->getStatusCode());
        PHPUnit::assertNotEquals('ACTIVE', $motTestData->getBody()['data']['status']);
    }

    /**
     * @When /^I start a Contingency MOT test$/
     */
    public function iStartAContingencyMOTTest()
    {
        $testClass = 4;
        $vehicleId = $this->vehicleContext->createVehicle(['testClass' => $testClass]);
        $this->contingencyTestContext->createContingencyCode();

        $emergencyLogId = $this->contingencyTestContext->getEmergencyLogId();

        $this->motTestData = $this->contingencyTest->startContingencyTest(
            $this->sessionContext->getCurrentAccessToken(),
            $emergencyLogId,
            $vehicleId,
            $testClass
        );
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

        $testClass = 4;
        $vehicleId = $this->vehicleContext->createVehicle(['testClass' => $testClass]);

        $this->contingencyTestContext->createContingencyCode(Authentication::CONTINGENCY_CODE_DEFAULT, 'SO', $dateTime);

        try {
            $emergencyLogId = $this->contingencyTestContext->getEmergencyLogId();
            $this->motTestData = $this->contingencyTest->startContingencyTestOnDateAtTime(
                $this->sessionContext->getCurrentAccessToken(),
                $emergencyLogId,
                $vehicleId,
                $testClass,
                $dateTime
            );
        } catch (\LogicException $e) {
        }
    }

    /**
     * @Given /^the Contingency Test is Logged$/
     */
    public function theContingencyTestIsLogged()
    {
        $actual = $this->motTest->getMotData($this->sessionContext->getCurrentAccessToken(), $this->getMotTestNumber());

        PHPUnit::assertEquals($this->contingencyTestContext->getContingencyCode(), $actual->getBody()['data']['emergencyLog']['number'], 'Contingency Code not returned.');
        PHPUnit::assertEquals($this->contingencyTestContext->getEmergencyLogId(), $actual->getBody()['data']['emergencyLog']['id'], 'Emergency Log Id not returned.');
    }

    /**
     * @Then I am unable to start a new Demo MOT test
     */
    public function iAmUnableToStartANewDemoMotTest()
    {
        $this->getMotTestNumber(); // you already have a demo test in progress
        $this->iStartDemoTest(); // try to create another one
        // We cannot create another demo test as long as the previous one is not finished
        PHPUnit::AssertSame(
            'You have a demo test that is already in progress', $this->motTestData->getBody()['errors'][0]['message']
        );
    }

    /**
     * @return string
     */
    public function getMotTestNumber()
    {
        if (!$this->motTestData) {
            throw new \BadMethodCallException('There is no MOT test in progress');
        }

        return $this->motTestData->getBody()['data']['motTestNumber'];
    }

    /**
     * @Then /^the recorded IP is "([^"]*)"$/
     */
    public function theRecordedIPIs($clientIp)
    {
        $actualClientIp = $this->statusData->getBody()['data']['clientIp'];

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
     *
     * @param $number
     */
    public function ICreateMotTests($number = 1)
    {
        for ($i=0; $i < $number; $i++) {
            $this->startMotTest($this->sessionContext->getCurrentUserId(), $this->sessionContext->getCurrentAccessToken());
            $this->motTest->abort(
                $this->sessionContext->getCurrentAccessToken(),
                $this->motTest->getInProgressTestId(
                    $this->sessionContext->getCurrentAccessToken(),
                    $this->sessionContext->getCurrentUserId()
                )
            );
        }
    }

    /**
     * @When /^I search for a vehicle without a manufactured date and first used date$/
     *
     */
    public function ISearchForAVehicleWithoutAManufacturerAndFirstUsedDate()
    {
        $vehicleData =  [
            'dateOfFirstUse' => null,
            'manufactureDate' => null,
            'vin' => 'WF0BXXGAJB1R41234',
            'registrationNumber' => 'F50GGP'
        ];

        $this->vehicleId = $this->vehicleContext->createVehicle($vehicleData);

        // throws exception if not found
        PHPUnit::assertNotNull($this->vehicleContext->getCurrentVehicleData());
    }

    /**
     * @Then /^manufactured date and first used date should be displayed as unknown$/
     *
     */
    public function manufacturerAndFirstUsedDateShouldBeDisplayedAsNotKnown()
    {
        $data = $this->vehicleContext->getCurrentVehicleData();

        PHPUnit::assertNull($data['data']['firstUsedDate']);
        PHPUnit::assertNull($data['data']['manufactureDate']);
    }

    /**
     * @Given /^I attempt to create a MOT Test on a vehicle without a manufactured date and first used date$/
     *
     */
    public function IAttemptToCreateAnMOTTestOnAVehicleWithoutAManufacturerAndFirstUsedDate()
    {
        $vehicleData =  [
            'dateOfFirstUse' => null,
            'manufactureDate' => null,
            'vin' => 'WF0BXXGAJB1R41234',
            'registrationNumber' => 'F50GGP'
        ];

        $this->vehicleId = $this->vehicleContext->createVehicle($vehicleData);

        $this->motTestData = $this->motTest->startNewMotTestWithVehicleId(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId(),
            $this->vehicleId
        );
    }

    /**
     * @Then /^MOT test should be created successfully$/
     *
     */
    public function MOTTestShouldBeCreatedSuccessfully()
    {
        PHPUnit::assertNotNull($this->motTestData);
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

            $this->personContext->createTester(["username" => $username]);
            $this->createPassedMotTest($this->personContext->getPersonUserId(), $this->personContext->getPersonToken());
            $number--;
        }
    }

    /**
     * @Given :number passed MOT tests have been created for the same vehicle
     */
    public function passedMotTestsHaveBeenCreatedForTheSameVehicle($number)
    {
        $vehicleId = $this->vehicleContext->createVehicle();

        $this->personContext->createTester(["username" => "tester".$this->testSupportHelper->getDataGeneratorHelper()->generateRandomString(10)]);

        while ($number) {
            $this->createPassedMotTest($this->personContext->getPersonUserId(), $this->personContext->getPersonToken(), $vehicleId);
            $this->motTestNumbers[]=$this->getMotTestNumber();
            $this->behatWait();
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

    public function createPassedMotTest($userId, $token, $vehicleId = null)
    {
        $this->startMotTest($userId, $token, [], $vehicleId);
        $this->brakeTestResult->addBrakeTestDecelerometerClass3To7($token, $this->getMotTestNumber());
        $this->odometerReading->addMeterReading($token, $this->getMotTestNumber(), date('Gis'), 'km');
        $this->motTest->passed(
            $token,
            $this->getMotTestNumber()
        );
    }

    /**
     * @Given a MOT test for vehicle with the following data exists:
     */
    public function aMotTestForVehicleWithTheFollowingDataExists(TableNode $table)
    {
        $this->personContext->createTester();
        $hash = $table->getColumnsHash();

        if (count($hash) !== 1) {
            throw new \InvalidArgumentException(sprintf('Expected a single vehicle record but got: %d', count($hash)));
        }

        $row = $hash[0];

        $vehicleData = [
            'registrationNumber' => $this->vehicle->randomRegNumber(),
            'vin' => $this->vehicle->randomVin(),
            'make' => $row['make_code'],
            'makeOther' => $row['make_other'],
            'model' => null,
            'modelOther' => $row['model_other']
        ];

        $this->vehicleId = $this->vehicleContext->createVehicle($vehicleData);

        $this->createPassedMotTest(
            $this->personContext->getPersonUserId(),
            $this->personContext->getPersonToken(),
            $this->vehicleId
        );
    }

    /**
     * @Given vehicle has a Re-Test test started
     */
    public function vehicleHasMotTestReTestStarted()
    {
        $this->sessionContext->iAmLoggedInAsATester();

        $this->vehicleHasMotTestFailed();

        $this->startMotTest($this->sessionContext->getCurrentUserId(),
            $this->sessionContext->getCurrentAccessToken(),
            ['motTestType' => MotTestTypeCode::RE_TEST],
            !empty($this->vehicleId) ? $this->vehicleId : null
        );
    }

    protected function vehicleHasMotTestStarted($testType)
    {
        $this->sessionContext->iAmLoggedInAsATester();

        $this->startMotTest($this->sessionContext->getCurrentUserId(),
            $this->sessionContext->getCurrentAccessToken(),
            ['motTestType' => $testType],
            !empty($this->vehicleId) ? $this->vehicleId : null
        );
    }


    public function vehicleHasMotTestFailed()
    {
        $this->startMotTest($this->sessionContext->getCurrentUserId(),
            $this->sessionContext->getCurrentAccessToken()
        );
        $this->odometerReading->addMeterReading($this->sessionContext->getCurrentAccessToken(), $this->motTest->getLastMotTestNumber(), 658, 'mi');
        $this->brakeTestResult->addBrakeTestDecelerometerClass3To7($this->sessionContext->getCurrentAccessToken(), $this->motTest->getLastMotTestNumber());
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
        $this->sessionContext->iAmLoggedInAsATester();

        $this->vehicleHasMotTestFailed();

        $this->startMotTest($this->sessionContext->getCurrentUserId(),
            $this->sessionContext->getCurrentAccessToken(),
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
        $this->sessionContext->iAmLoggedInAsATester();

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
        $this->sessionContext->iAmLoggedInAsATester();

        $this->startMotTest($this->sessionContext->getCurrentUserId(),
            $this->sessionContext->getCurrentAccessToken(),
            [
                'motTestType'      => MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST,
                'vehicleStationId' => null
            ]
        );
    }

    /**
     * @Given vehicle has a Non-Mot Test test started
     */
    public function vehicleHasANonMotTestTestStarted()
    {
        $this->vehicleHasMotTestStarted(MotTestTypeCode::NON_MOT_TEST);
    }

    /**
     * @return array
     */
    public function getMotTestData()
    {
        return $this->statusData->getBody()['data'];
    }

    /**
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function getRawMotTestData()
    {
        return $this->motTestData;
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
                $motTest = $this->motTest->getMotData($this->sessionContext->getCurrentAccessToken(), $motTestNumber)->getBody()->toArray()['data'];
                $this->motTests[] = $motTest;
            }
        }

        usort($this->motTests, function($a, $b) {
            return $a['id'] < $b['id'];
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
        $response = $this->reasonForRejection->addPrs(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getMotTestNumber()
        );

        PHPUnit::assertSame(200, $response->getStatusCode());
    }

    /**
     * @Given /^I can add a Failure to test$/
     */
    public function iCanAddAFailureToTest()
    {
        $response = $this->reasonForRejection->addFailure(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getMotTestNumber()
        );

        PHPUnit::assertSame(200, $response->getStatusCode());
    }

    /**
     * @Given /^I can edit previously added Rfr$/
     */
    public function iCanEditPreviouslyAddedRfr()
    {
        $response = $this->reasonForRejection->editRFR(
            $this->sessionContext->getCurrentAccessToken(),
            $this->getMotTestNumber(),
            $this->history->getLastResponse()->getBody()['data']
        );

        PHPUnit::assertSame(200, $response->getStatusCode());
    }

    /**
     * @Then I can not start an Mot Test for Vehicle with class :vehilceClass
     */
    public function iCanNotStartAnMotTestForVehicleWithClass($vehicleClass)
    {
        $vehicleId = $this->vehicleContext->createVehicle(['testClass' => $vehicleClass]);

        $this->motTestData = $this->motTest->startNewMotTestWithVehicleId(
            $this->sessionContext->getCurrentAccessToken(),
            $this->sessionContext->getCurrentUserId(),
            $vehicleId,
            $vehicleClass,
            ["vehicleTestingStationId" => $this->vtsContext->getSite()["id"]]
        );

        PHPUnit::assertSame(403, $this->motTestData->getStatusCode());

        $expectedError = sprintf("Your Site is not authorised to test class %s vehicles", $vehicleClass);
        $apiErrors = $this->motTestData->getBody()->offsetGet("errors")->toArray();
        $error = array_shift($apiErrors)["message"];

        PHPUnit::assertSame($expectedError, $error);
    }
}
