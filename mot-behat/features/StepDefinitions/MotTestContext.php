<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\TableNode;
use Dvsa\Mot\Behat\Support\Api\NonMotTest;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\Session;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\VehicleData;
use Dvsa\Mot\Behat\Support\Data\SiteData;
use Dvsa\Mot\Behat\Support\Data\AuthorisedExaminerData;
use Dvsa\Mot\Behat\Support\Data\UserData;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use Dvsa\Mot\Behat\Support\Data\ContingencyMotTestData;
use Dvsa\Mot\Behat\Support\Data\OdometerReadingData;
use Dvsa\Mot\Behat\Support\Data\Params\MotTestParams;
use Dvsa\Mot\Behat\Support\Data\Params\VehicleParams;
use Dvsa\Mot\Behat\Support\Data\Params\PersonParams;
use Dvsa\Mot\Behat\Support\Data\Params\SiteParams;
use Dvsa\Mot\Behat\Support\Data\Exception\UnexpectedResponseStatusCodeException;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use Dvsa\Mot\Behat\Support\Data\ContingencyData;
use Zend\Http\Response as HttpResponse;
use PHPUnit_Framework_Assert as PHPUnit;

class MotTestContext implements Context, SnippetAcceptingContext
{
    /**
     * @var MotTest
     */
    private $motTest;

    /**
     * @var NonMotTest
     */
    private $nonMotTest;

    /**
     * @var array
     */
    private $motTests;

    /**
     * @var TestSupportHelper
     */
    private $testSupportHelper;

    /**
     * @var array
     */
    private $motTestNumbers;

    /**
     * @var CertificateContext
     */
    private $certificateContext;

    private $vehicleData;

    private $siteData;

    private $authorisedExaminerData;

    private $userData;

    /**
     * @var MotTestData
     */
    private $motTestData;

    private $contingencyData;

    private $contingencyMotTestData;

    private $odometerReadingData;

    public function __construct(
        NonMotTest $nonMotTest,
        MotTest $motTest,
        TestSupportHelper $testSupportHelper,
        VehicleData $vehicleData,
        SiteData $siteData,
        AuthorisedExaminerData $authorisedExaminerData,
        UserData $userData,
        MotTestData $motTestData,
        ContingencyData $contingencyData,
        ContingencyMotTestData $contingencyMotTestData,
        OdometerReadingData $odometerReadingData
    ) {
        $this->nonMotTest = $nonMotTest;
        $this->motTest = $motTest;
        $this->testSupportHelper = $testSupportHelper;
        $this->vehicleData = $vehicleData;
        $this->siteData = $siteData;
        $this->authorisedExaminerData = $authorisedExaminerData;
        $this->userData = $userData;
        $this->motTestData = $motTestData;
        $this->contingencyData = $contingencyData;
        $this->contingencyMotTestData = $contingencyMotTestData;
        $this->odometerReadingData = $odometerReadingData;
    }

    /**
     * @Given I start an Mot Test with a Class :testClass Vehicle
     * @When I start an MOT Test
     * @Then I should be able to test vehicles
     * @When I have an MOT Test In Progress
     */
    public function iStartMotTest($testClass = VehicleClassCode::CLASS_4)
    {
        $this->motTestData->create(
            $this->userData->getCurrentLoggedUser(),
            $this->vehicleData->create($testClass),
            $this->siteData->get()
        );
    }

    /**
     * @When I start a non-MOT Test
     */
    public function iStartNonMotTest()
    {
        return  $this->motTestData->create(
            $this->userData->getCurrentLoggedUser(),
            $this->vehicleData->create(VehicleClassCode::CLASS_4),
            $this->siteData->get(),
            MotTestTypeCode::NON_MOT_TEST
        );
    }

    /**
     * @When I try to start an Mot Test for a class :testClass vehicle
     * @Given I try to start an Mot Test with a Class :testClass Vehicle
     */
    public function iTryStartMotTest($testClass = VehicleClassCode::CLASS_4)
    {
        try {
            $this->iStartMotTest($testClass);
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @Given there is a Mot test with :testType type in progress
     * @Given there is a Mot test in progress
     */
    public function thereIsAMotTestWithTypeInProgress($testType = MotTestTypeCode::NORMAL_TEST)
    {
        $this->motTestData->create(
            $this->userData->createTester("Mike Tyson"),
            $this->vehicleData->create(),
            $this->siteData->get(),
            $testType
        );
    }

    /**
     * @When the Tester Passes the Mot Test
     */
    public function theTesterPassesTheMotTest()
    {
        $this->motTestData->passMotTest($this->motTestData->getLast());

    }

    /**
     * @When the Tester tries pass the Mot Test
     */
    public function theTesterTriesPassTheMotTest()
    {
        try {
            $this->motTestData->passMotTest($this->motTestData->getLast());
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @When /^the Tester Fails the Mot Test$/
     */
    public function theTesterFailsTheMotTest()
    {
        $this->motTestData->failMotTest($this->motTestData->getAll()->last());
    }

    /**
     * @When /^the Tester tries fail the Mot Test$/
     */
    public function theTesterTriesFailTheMotTest()
    {
        try {
            $this->motTestData->failMotTest($this->motTestData->getAll()->last());
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }
            PHPUnit::assertTrue(isset($exception), "Exception not thrown");
            PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @When /^the Tester Passes the Mystery Shopper Test$/
     */
    public function theTesterPassesTheMysteryShopperTest()
    {
        /** @var MotTestDto $mot */
        $mot = $this->motTestData->getAll()->last();

        try {
            $this->motTestData->passMotTest($mot);
        } catch (\Exception $e) {

        }

        PHPUnit::assertEquals('MS', $mot->getTestType()->getCode());
    }

    /**
     * @When /^the Tester Aborts the Mot Test$/
     */
    public function theTesterAbortsTheMotTest()
    {
        $this->motTestData->abortMotTest($this->motTestData->getLast());
    }

    /**
     * @When /^the Tester tries abort the Mot Test$/
     */
    public function theTesterTriesAbortTheMotTest()
    {
        try {
            $this->theTesterAbortsTheMotTest();
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @When I abort the Mot Test
     */
    public function theUserAbortsTheMotTest()
    {
        $mot = $this->motTestData->getLast();
        $this->motTestData->abortMotTestByUser($mot, $this->userData->getCurrentLoggedUser());
    }

    /**
     * @When I try abort the Mot Test
     */
    public function theUserTriesAbortTheMotTest()
    {
        try {
            $this->theUserAbortsTheMotTest();
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
    }

    /**
     * @When /^a logged in Vehicle Examiner aborts the test$/
     * @When /^as a Vehicle Examiner I abort the test$/
     */
    public function anAuthenticatedVehicleExaminerAbortsTheTest()
    {
        $user = $this->userData->createVehicleExaminer();
        $this->motTestData->abortMotTestByVE($this->motTestData->getLast(), $user);
    }

    /**
     * @Given the Tester cancels the test with a reason of :reasonForCancelId
     */
    public function theTesterCancelsTheTestWithAReasonOf($reasonForCancelId)
    {
        $mot = $this->motTestData->getLast();
        $this->motTestData->abandonMotTest($mot, $reasonForCancelId);
    }

    /**
     * @Then I can complete a Demo test for vehicle class :vehicleClassCode
     * @Given I have passed a Demo MOT test
     *
     * @param string $vehicleClassCode
     */
    function iCanCompleteDemoTestForVehicleClass($vehicleClassCode = VehicleClassCode::CLASS_4)
    {
        $this->motTestData->createPassedMotTest(
            $this->userData->getCurrentLoggedUser(),
            null,
            $this->vehicleData->create($vehicleClassCode),
            MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING
        );
    }

    /**
     * @When I perform :testCount demotests
     */
    public function iPerformDemotests($testCount)
    {
        for($i = 0; $i < $testCount; $i++) {
            $this->iCanCompleteDemoTestForVehicleClass();
        }
    }

    /**
     * @Given vehicle has a Non-Mot Test test started
     */
    public function vehicleHasANonMotTestTestStarted()
    {
        $this->vehicleHasMotTestStarted(MotTestTypeCode::NON_MOT_TEST);
    }

    /**
     * @Given I pass Mot Test with a Class :testClass Vehicle
     * @Given I have passed an MOT test
     */
    public function IPassMotTestWithAClassVehicle($testClass = VehicleClassCode::CLASS_4)
    {
        return $this->motTestData->createPassedMotTest(
            $this->userData->getCurrentLoggedUser(),
            $this->siteData->get(),
            $this->vehicleData->create($testClass)
        );
    }

    /**
     * @When I pass :testCount normal tests
     */
    public function iPassNormalTests($testCount)
    {
        for($i = 0; $i < $testCount; $i++) {
            $this->IPassMotTestWithAClassVehicle();
        }
    }

    /**
     * @Given there is a :testStatus MOT test
     * @Given there is a :testStatus :testType MOT test
     */
    public function thereIsAMot($testStatus = MotTestStatusCode::PASSED, $testType = MotTestTypeCode::NORMAL_TEST)
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
     * @When I fail :testCount normal tests
     * @When I fail normal tests
     */
    public function iFailNormalTests($testCount = 1)
    {
        for($i = 0; $i < $testCount; $i++) {
            $this->IFailMotTestWithAClassVehicle(VehicleClassCode::CLASS_4);
        }
    }

    /**
     * @When I perform :testCount retests
     */
    public function iPerformRetests($testCount)
    {
        for($i = 0; $i < $testCount; $i++) {
            $this->iFailNormalTests();

            $this->motTestData->createCompletedMotTest(
                $this->userData->getCurrentLoggedUser(),
                $this->siteData->get(),
                $this->vehicleData->getLast(),
                [MotTestParams::TYPE => MotTestTypeCode::RE_TEST, MotTestParams::STATUS => MotTestStatusCode::FAILED]
            );
        }
    }

    /**
     * @When I start and abort :testCount tests
     */
    public function iStartAndAbortTests($testCount)
    {
        for($i = 0; $i < $testCount; $i++) {
            $this->motTestData->createAbandonedMotTest(
                $this->userData->getCurrentLoggedUser(),
                $this->siteData->get(),
                $this->vehicleData->create()
            );
        }
    }

    /**
     * @Given /^I start an Mot Test with a Masked Vehicle$/
     */
    public function iStartAnMotTestWithAMaskedVehicle()
    {
        $tester = $this->userData->getCurrentLoggedUser();
        $ve = $this->userData->createVehicleExaminer();

        $vehicle = $this->vehicleData->createMaskedVehicleWithParams($tester->getAccessToken(), $ve->getAccessToken());

        $mot = $this->motTestData->create($tester, $vehicle, $this->siteData->get(), MotTestTypeCode::MYSTERY_SHOPPER);

        PHPUnit::assertInstanceOf(MotTestDto::class, $mot);
    }

    /**
     * @When /^I pass an MOT Test on a Masked Vehicle$/
     */
    public function iPassAnMotTestOnAMaskedVehicle()
    {
        $this->passMotTestOnAMaskedVehicle($this->userData->getCurrentLoggedUser(), $this->siteData->get());
    }

    /**
     * @When another Tester passes an MOT Test on a Masked Vehicle at :vtsName
     */
    public function anotherTesterPassesAnMotTestOnAMaskedVehicleAt($vtsName)
    {
        $vts = $this->siteData->get($vtsName);

        $tester = $this->userData->createTesterAssignedWitSite($vts->getId(), "another tester");

        $this->passMotTestOnAMaskedVehicle($tester, $vts);
    }

    private function passMotTestOnAMaskedVehicle(AuthenticatedUser $user, SiteDto $vts)
    {
        $ve = $this->userData->createVehicleExaminer();

        $vehicle = $this->vehicleData->createMaskedVehicleWithParams($user->getAccessToken(), $ve->getAccessToken());

        $mot = $this->motTestData->createPassedMotTest($user, $vts, $vehicle, MotTestTypeCode::MYSTERY_SHOPPER);

        PHPUnit::assertInstanceOf(MotTestDto::class, $mot);
    }

    /**
     * @When the vehicle from the previous MOT Test is unmasked
     */
    public function theVehicleIsUnmasked()
    {
        $ve = $this->userData->createVehicleExaminer();
        $mot = $this->motTestData->getLast();

        $this->vehicleData->unmaskVehicle($mot->getVehicle()->getId(), $ve->getAccessToken());
    }

    /**
     * @Then /^the MOT Test Status is "([^"]*)"$/
     * @Then /^the MOT Test Status should be "([^"]*)"$/
     *
     * @param $status
     */
    public function theMOTTestStatusIs($status)
    {
        $actualStatus = $this->motTestData->getLast()->getStatus();

        PHPUnit::assertEquals($status, $actualStatus, 'MOT Test Status is incorrect');
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
     * @Then /^I should receive the MOT test number$/
     */
    public function iShouldReceiveTheMotTestNumber()
    {
        $this->motTestData->getInProgressTest($this->userData->getCurrentLoggedUser());
    }

    /**
     * @Given /^the MOT Test Number should be (\d+) digits long$/
     *
     * @param $length
     */
    public function theMOTTestNumberShouldBeDigitsLong($length)
    {
        PHPUnit::assertEquals($length, strlen($this->motTestData->getLast()->getMotTestNumber()), 'MOT Test number is not 12 digits long.');
    }

    /**
     * @Then I can view the MOT test summary
     */
    public function iCanViewTheMotSummary()
    {
        $mot = $this->motTestData->getLast();

        $motTestData = $this->motTestData->fetchMotTestData($this->userData->getCurrentLoggedUser(), $mot->getMotTestNumber());
        PHPUnit::assertNotEquals(MotTestStatusName::ACTIVE, $motTestData->getStatus());
    }

    /**
     * @Given /^I create (.*) mot tests$/
     * @Given /^I create an mot test$/
     * @Given /^I have created (.*) mot tests$/
     * @Given I have created :number mot tests for :siteName site
     *
     * @param int $number
     * @param string $siteName
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
        ];

        $user = $this->userData->getCurrentLoggedUser();
        $vehicle = $this->vehicleData->createWithParams($user->getAccessToken(), $vehicleData);
        $this->motTestData->create(
            $user,
            $vehicle,
            $this->siteData->get()
        );
    }

    /**
     * @Then /^MOT test should be created successfully$/
     *
     */
    public function MOTTestShouldBeCreatedSuccessfully()
    {
        $response = $this->motTestData->getNormalMotTestLastResponse();
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_200, $response->getStatusCode());
    }

    /**
     * @Given :number passed MOT tests have been created for the same vehicle
     */
    public function passedMotTestsHaveBeenCreatedForTheSameVehicle($number)
    {
        $tester = $this->userData->createTester();

        while ($number) {
            $this->motTestData->createPassedMotTest(
                $tester,
                $this->siteData->get(),
                $this->vehicleData->getLast()
            );

            $number--;
        }
    }

    /**
     * @Given :number failed MOT tests have been created for the same vehicle
     */
    public function failedMotTestsHaveBeenCreatedForTheSameVehicle($number)
    {
        $tester = $this->userData->createTester();

        while ($number) {
            $this->motTestData->createFailedMotTest(
                $tester,
                $this->siteData->get(),
                $this->vehicleData->getLast()
            );

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
            VehicleParams::MAKE => $row['make_id'],
            VehicleParams::MAKE_OTHER => $row['make_other'],
            VehicleParams::MODEL => null,
            VehicleParams::MODEL_OTHER => $row['model_other']
        ];

        $tester = $this->userData->createTesterAssignedWitSite($this->siteData->get()->getId(), 'Vehicle Constructor');
        $vehicle = $this->vehicleData->createWithParams($tester->getAccessToken(), $vehicleData);

        $this->motTestData->createPassedMotTest($tester, $this->siteData->get(), $vehicle);
    }

    /**
     * @Given vehicle has a Re-Test test started
     */
    public function vehicleHasMotTestReTestStarted()
    {
        $this->vehicleHasMotTestStarted(MotTestTypeCode::RE_TEST);
    }

    protected function vehicleHasMotTestStarted($testType, VehicleDto $vehicle = null)
    {
        if ($vehicle === null) {
            $vehicle = $this->vehicleData->create();
        }

        $this->motTestData->create(
            $this->userData->createTester(),
            $vehicle,
            $this->siteData->get(),
            $testType
        );
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
     * @Given vehicle has a Routine Demonstration Test test started
     */
    public function vehicleHasARoutineDemonstrationTestTestStarted()
    {
        $this->vehicleHasMotTestStarted(MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING);
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
                $motTest = $this->motTest->getMotData($this->userData->getCurrentLoggedUser()->getAccessToken(), $motTestNumber)->getBody()->getData();
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
     * @Then I can not start an Mot Test for Vehicle with class :vehilceClass
     */
    public function iCanNotStartAnMotTestForVehicleWithClass($vehicleClass)
    {
        $this->iCanNotStartAnMotTestForVehicleWithClassAtSite($vehicleClass, $this->siteData->get());
    }

    /**
     * @Then I can not start an Mot Test for Vehicle with class :vehilceClass at site :site
     */
    public function iCanNotStartAnMotTestForVehicleWithClassAtSite($vehicleClass, SiteDto $site)
    {
        $vehicle = $this->vehicleData->create($vehicleClass);

        try {
            $this->motTestData->create(
                $this->userData->getCurrentLoggedUser(),
                $vehicle,
                $site

            );
            $response = $this->motTestData->getNormalMotTestLastResponse();
        } catch (UnexpectedResponseStatusCodeException $exception) {
            $response = $this->motTestData->getNormalMotTestLastResponse();
        }

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
        PHPUnit::assertSame(HttpResponse::STATUS_CODE_403, $response->getStatusCode());

        $expectedError = sprintf("Your Site is not authorised to test class %s vehicles", $vehicleClass);
        $apiErrors = $response->getBody()->offsetGet("errors")->toArray();
        $error = array_shift($apiErrors)["message"];

        PHPUnit::assertSame($expectedError, $error);
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

    /**
     * @Then I should not be able to test vehicles
     */
    public function iShouldNotBeAbleToTestVehicles()
    {
        try {
            $this->iStartMotTest();
        } catch (UnexpectedResponseStatusCodeException $exception) {

        }

        $actualStatusCode = $this->motTestData->getLastResponse()->getStatusCode();

        PHPUnit::assertTrue(isset($exception), "Exception not thrown");
        PHPUnit::assertInstanceOf(UnexpectedResponseStatusCodeException::class, $exception);
        PHPUnit::assertEquals(HttpResponse::STATUS_CODE_403, $actualStatusCode);
    }

    /**
     * @Then I should be able to find the MOT Test certificate for reprinting
     * @Then I should still be able to find the MOT Test certificate for reprinting
     */
    public function iShouldSeeAnEntryInTheTestHistory()
    {
        PHPUnit::assertCount(1, $this->getTestHistoryForVehicleInLastMotTest());
    }

    /**
     * @Then I should not be able to find the MOT Test certificate for reprinting
     */
    public function iShouldNotSeeAnEntryInTheTestHistory()
    {
        PHPUnit::assertCount(0, $this->getTestHistoryForVehicleInLastMotTest());
    }

    private function getTestHistoryForVehicleInLastMotTest()
    {
        $mot = $this->motTestData->getLast();

        return $this->motTestData->getTestHistory(
            $mot->getVehicle()->getId(),
            $this->userData->getCurrentLoggedUser()
        );
    }
}
