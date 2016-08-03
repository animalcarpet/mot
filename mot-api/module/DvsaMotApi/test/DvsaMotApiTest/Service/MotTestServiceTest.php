<?php

namespace DvsaMotApiTest\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Constants\Role;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Mapper\OdometerReadingMapper;
use DvsaCommonTest\TestUtils\MockHandler;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\BrakeTestResultClass12;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\FuelType;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\OdometerReading;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntitiesTest\Entity\MotTestReasonForRejectionTest;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityCheckCode;
use DvsaMotApiTest\Factory\PersonObjectsFactory;
use PHPUnit_Framework_MockObject_MockObject;
use DvsaEntities\Entity\CertificateReplacement;

/**
 * Unit test for MotTestService
 */
class MotTestServiceTest extends AbstractMotTestServiceTest
{
    use TestCasePermissionTrait;

    const VEHICLE_CLASS_BELOW_3 = true;
    const VEHICLE_CLASS_4_OR_ABOVE = false;

    const BRAKE_TEST_PASSED = true;
    const BRAKE_TEST_FAILED = false;

    const SLOTS_COUNT_START = 32;

    // 12 digits number
    const MOT_TEST_NUMBER = "123456789012";

    const VEHICLE_ID = 9999;
    const VEHICLE_ID_ENC = 'jq33IixSpBsx4rglOvxByg';

    public function testGetMotTestDataThrowsNotFoundExceptionForNullFind()
    {
        $mocks = $this->getMocksForMotTestService();
        $this->mockGetMotTestByTestNumber($this->mockMotTestRepository, new MotTest());

        $motTest = $this->getMotTestEntity(self::MOT_TEST_NUMBER);
        $motTest->getVehicle()->setId(1);

        // Mock Repo to return above object
        $this->mockGetMotTestByTestNumber($this->mockMotTestRepository, $motTest);

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);
        $motTestService->getMotTestData(self::MOT_TEST_NUMBER);
    }

    public function testGetMotTestDataWithEmergencyNoException()
    {
        $motTest = new MotTest();
        $motTest->setEmergencyLog(true);
        $motTest->setStartedDate(new \DateTime('now'));
        $motTest->setIssuedDate((new \DateTime('now'))->add(new \DateInterval('P1D')));
        $mocks = $this->getMocksForMotTestService(null, false);

        $this->mockMethod($this->mockAuthService, 'isGranted', null, false);
        $this->mockGetMotTestByTestNumber($this->mockMotTestRepository, $motTest);

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $motTestService->getMotTestData(self::MOT_TEST_NUMBER);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\ForbiddenException
     */
    public function testMOTRestValidateNoVehicleThrowsForbiddenException()
    {
        $motTest = new MotTest();
        $motTest->setEmergencyLog(true);
        $motTest->setStartedDate(new \DateTime('now'));
        $motTest->setIssuedDate((new \DateTime('now'))->add(new \DateInterval('P1D')));
        $testStatus = $this->getMock('\DvsaEntities\Entity\MotTestStatus');

        $testStatus->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(MotTestStatusName::ACTIVE));

        $vehicle = new Vehicle();

        $motTest->setStatus($testStatus);

        $mocks = $this->getMocksForMotTestService(null, false);

        $this->mockMethod($this->mockAuthService, 'isGranted', null, false);
        $this->mockGetMotTestByTestNumber($this->mockMotTestRepository, $motTest);

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $motTestService->getMotTestDataForRetest(self::MOT_TEST_NUMBER);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\ForbiddenException
     */
    public function testMOTRestValidateNotFailedThrowsForbiddenException()
    {
        $motTest = new MotTest();
        $motTest->setEmergencyLog(true);
        $motTest->setStartedDate(new \DateTime('now'));
        $motTest->setIssuedDate((new \DateTime('now'))->add(new \DateInterval('P1D')));
        $testStatus = $this->getMock('\DvsaEntities\Entity\MotTestStatus');

        $testStatus->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(MotTestStatusName::ACTIVE));

        $vehicle = new Vehicle();

        $motTest->setStatus($testStatus);
        $motTest->setVehicle($vehicle);

        $mocks = $this->getMocksForMotTestService(null, false);

        $this->mockMethod($this->mockAuthService, 'isGranted', null, false);
        $this->mockGetMotTestByTestNumber($this->mockMotTestRepository, $motTest);

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $motTestService->getMotTestDataForRetest(self::MOT_TEST_NUMBER);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\ForbiddenException
     */
    public function testMOTRestValidateIsCancelledThrowsForbiddenException()
    {
        $motTest = new MotTest();
        $motTest->setEmergencyLog(true);
        $motTest->setStartedDate(new \DateTime('now'));
        $motTest->setIssuedDate((new \DateTime('now'))->add(new \DateInterval('P1D')));

        $testStatus = $this->getMock('\DvsaEntities\Entity\MotTestStatus');

        /*
         *  isCancelled checks for:
         *  MotTestStatusName::ABANDONED,
         *  MotTestStatusName::ABORTED,
         *  MotTestStatusName::ABORTED_VE
        */

        $testStatus->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(MotTestStatusName::ABANDONED));

        $vehicle = new Vehicle();

        $motTest->setStatus($testStatus);
        $motTest->setVehicle($vehicle);

        $mocks = $this->getMocksForMotTestService(null, false);

        $this->mockMethod($this->mockAuthService, 'isGranted', null, false);
        $this->mockGetMotTestByTestNumber($this->mockMotTestRepository, $motTest);

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $motTestService->getMotTestDataForRetest(self::MOT_TEST_NUMBER);
    }

    public function testMOTRestValidateNoException()
    {
        $motTest = new MotTest();
        $motTest->setEmergencyLog(true);
        $motTest->setStartedDate(new \DateTime('now'));
        $motTest->setIssuedDate((new \DateTime('now'))->add(new \DateInterval('P1D')));
        $testStatus = $this->getMock('\DvsaEntities\Entity\MotTestStatus');

        $testStatus->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(MotTestStatusName::FAILED));

        $vehicle = new Vehicle();

        $motTest->setStatus($testStatus);
        $motTest->setVehicle($vehicle);

        $mocks = $this->getMocksForMotTestService(null, false);

        $this->mockMethod($this->mockAuthService, 'isGranted', null, false);
        $this->mockGetMotTestByTestNumber($this->mockMotTestRepository, $motTest);

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $motTestService->getMotTestDataForRetest(self::MOT_TEST_NUMBER);
    }

    public function testCreateMotTest()
    {
        $this->markTestSkipped();
        $vehicleId = 2;
        $vehicleTestingStationId = 3;
        $primaryColourId = 1;
        $fuelTypeId = 21;
        $vehicleClassCode = 22;
        $secondaryColourId = 2;
        $hasRegistration = true;

        $model = new Model();
        $make = new Make();
        $model->setMake($make);
        $vehicle = new Vehicle();
        $vehicle->setModel($model);
        $vehicleTestingStation = new Site();
        $primaryColour = new Colour();
        $secondaryColour = new Colour();
        $fuelType = new FuelType();
        $vehicleClass = new VehicleClass();
        $this->addOrg($vehicleTestingStation);

        $mocks = $this->getMocksForMotTestService();

        $mockPerson = PersonObjectsFactory::person(['isTester' => true]);

        $mockTesterServiceHandler = new MockHandler($this->mockTesterService, $this);
        $mockTesterServiceHandler
            ->next('verifyAndApplyTesterIsActive')
            ->with($mockPerson)
            ->willReturn(true);

        $entityMockHandler = new MockHandler($this->mockEntityManager, $this);

        $entityMockHandler->next('getRepository')
            ->with(MotTest::class)
            ->willReturn($this->mockMotTestRepository);

        $entityMockHandler->next('getRepository')
            ->with(MotTestType::class)
            ->willReturn($this->mockMotTestTypeRepository);

        $entityMockHandler->next('find')
            ->with(Site::class, $vehicleTestingStationId)
            ->willReturn($vehicleTestingStation);

        $entityMockHandler->next('find')
            ->with(Vehicle::class, $vehicleId)
            ->willReturn($vehicle);

        $entityMockHandler->next('find')
            ->with(Colour::class, $primaryColourId)
            ->willReturn($primaryColour);

        $entityMockHandler->next('find')
            ->with(Colour::class, $secondaryColourId)
            ->willReturn($secondaryColour);

        $entityMockHandler->next('find')
            ->with(FuelType::class, $fuelTypeId)
            ->willReturn($fuelType);

        $entityMockHandler->next('find')
            ->with(VehicleClass::class, $vehicleClassCode)
            ->willReturn($vehicleClass);

        $entityMockHandler->next('persist')
            ->with(
                $this->logicalAnd(
                    $this->isInstanceOf(MotTest::class),
                    $this->attributeEqualTo('tester', $mockPerson),
                    $this->attributeEqualTo('vehicle', $vehicle),
                    $this->attributeEqualTo('vehicleTestingStation', $vehicleTestingStation),
                    $this->attributeEqualTo('primaryColour', $primaryColour),
                    $this->attributeEqualTo('secondaryColour', $secondaryColour),
                    $this->attributeEqualTo('fuelType', $fuelType),
                    $this->attributeEqualTo('vehicleClass', $vehicleClass),
                    $this->attributeEqualTo('hasRegistration', $hasRegistration),
                    $this->attribute($this->matchesRegularExpression('/[A-Z0-9]{13}/'), 'number')
                )
            );

        $this->setupMockForSingleCall($this->mockEntityManager, 'flush', null);
        $this->setupMockForSingleCall($this->mockMotTestValidator, 'validateNewMotTest', true);

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $result = $motTestService->createMotTest(
            $vehicleId,
            $vehicleTestingStationId,
            $primaryColourId,
            $secondaryColourId,
            $fuelTypeId,
            $vehicleClassCode,
            $hasRegistration,
            null,
            MotTestTypeCode::NORMAL_TEST
        );

        $this->assertInstanceOf(MotTest::class, $result, 'Should return the persisted MOT Test object');
    }

    public function testCreateMotTestNotEligibleOfRetestAsOriginalCancelledShouldThrowBadRequestException()
    {
        $this->markTestSkipped();
        $expectedCancelCode = RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_CANCELLED;
        $vehicleId = 2;
        $vehicleTestingStationId = 3;
        $primaryColour = 'Blue';
        $secondaryColour = null;
        $hasRegistration = true;

        $mocks = $this->getMocksForMotTestService();

        $entityMockHandler = new MockHandler($this->mockEntityManager, $this);
        $entityMockHandler->next('getRepository')
            ->with(MotTest::class)
            ->willReturn($this->mockMotTestRepository);

        $entityMockHandler->next('getRepository')
            ->with(MotTestType::class)
            ->willReturn($this->mockMotTestTypeRepository);

        $mockPerson = PersonObjectsFactory::person(['isTester' => true]);

        $mockTesterServiceHandler = new MockHandler($this->mockTesterService, $this);
        $mockTesterServiceHandler->next('verifyAndApplyTesterIsActive')
            ->with($mockPerson)
            ->willReturn(true);

        $this->mockMethod($this->mockRetestEligibilityValidator, 'checkVehicle', null, [$expectedCancelCode]);

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $caughtException = null;
        try {
            $motTestService->createMotTest(
                $vehicleId,
                $vehicleTestingStationId,
                $primaryColour,
                $secondaryColour,
                new FuelType(),
                new VehicleClass(),
                $hasRegistration,
                null,
                MotTestTypeCode::RE_TEST
            );
        } catch (BadRequestException $ex) {
            $caughtException = $ex;
        }
        $this->assertNotNull($caughtException, "Service should throw an exception with 1 error code");
        $error = $caughtException->getErrors()[0];
        $this->assertEquals(1, count($caughtException->getErrors()));
        $this->assertEquals($expectedCancelCode, $error['code']);
        $this->assertEquals("Original test was cancelled", $error['message']);
    }

    public function testCreateMotTestInProgressTestExistsShouldThrowBadRequestException()
    {
        $this->markTestSkipped();
        $vehicleId = 2;
        $vehicleTestingStationId = 3;
        $primaryColour = 'Blue';
        $secondaryColour = null;
        $hasRegistration = true;

        $mockPerson = PersonObjectsFactory::person(['id' => 101, 'isTester' => true]);

        $mocks = $this->getMocksForMotTestService();
        $entityMockHandler = new MockHandler($this->mockEntityManager, $this);

        $entityMockHandler->next('getRepository')
            ->with(MotTest::class)
            ->willReturn($this->mockMotTestRepository);

        $entityMockHandler->next('getRepository')
            ->with(MotTestType::class)
            ->willReturn($this->mockMotTestTypeRepository);

        $this->mockMotTestRepository->expects($this->once())
            ->method("findInProgressTestIdForVehicle")
            ->willReturn(1);

        $mockTesterServiceHandler = new MockHandler($this->mockTesterService, $this);
        $mockTesterServiceHandler->next('verifyAndApplyTesterIsActive')
            ->with($mockPerson)
            ->willReturn(true);

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $caughtException = null;
        try {
            $motTestService->createMotTest(
                $vehicleId,
                $vehicleTestingStationId,
                $primaryColour,
                $secondaryColour,
                new FuelType(),
                new VehicleClass(),
                $hasRegistration,
                null,
                MotTestTypeCode::RE_TEST
            );
        } catch (BadRequestException $ex) {
            $caughtException = $ex;
        }

        $this->assertNotNull($caughtException, "Service should throw an exception with 1 error code");
        $error = $caughtException->getErrors()[0];
        $this->assertEquals(1, count($caughtException->getErrors()));
        $this->assertEquals("Vehicle already has an in progress test", $error['message']);
    }

    public function testCreateMotTestInProgressTestForTesterShouldThrowBadRequestException()
    {
        $this->markTestSkipped();
        $vehicleId = 2;
        $vehicleTestingStationId = 3;
        $primaryColour = 'Blue';
        $secondaryColour = null;
        $hasRegistration = true;

        $mockPerson = PersonObjectsFactory::person(['id' => 101, 'isTester' => true]);

        $mocks = $this->getMocksForMotTestService();
        $entityMockHandler = new MockHandler($this->mockEntityManager, $this);

        $entityMockHandler->next('getRepository')
            ->with(MotTest::class)
            ->willReturn($this->mockMotTestRepository);

        $entityMockHandler->next('getRepository')
            ->with(MotTestType::class)
            ->willReturn($this->mockMotTestTypeRepository);

        $this->mockMotTestRepository->expects($this->once())
            ->method("findInProgressTestForPerson")
            ->willReturn(1);

        $mockTesterServiceHandler = new MockHandler($this->mockTesterService, $this);
        $mockTesterServiceHandler->next('verifyAndApplyTesterIsActive')
            ->with($mockPerson)
            ->willReturn(true);

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $caughtException = null;
        try {
            $motTestService->createMotTest(
                $vehicleId,
                $vehicleTestingStationId,
                $primaryColour,
                $secondaryColour,
                new FuelType(),
                new VehicleClass(),
                $hasRegistration,
                null,
                MotTestTypeCode::RE_TEST
            );
        } catch (BadRequestException $ex) {
            $caughtException = $ex;
        }

        $this->assertNotNull($caughtException, "Service should throw an exception with 1 error code");
        $error = $caughtException->getErrors()[0];
        $this->assertEquals(1, count($caughtException->getErrors()));
        $this->assertEquals("You have a test that is already in progress", $error['message']);
    }

    /**
     * This is the data provided for the test
     *
     * @dataProvider pendingStatusIncompleteTestRolesData
     */
    public function testGetPendingMotTestStatusIncomplete(
        $expectedStatus,
        $roleText,
        $odometerValue = null,
        $brakeTestResult = null,
        $rfrIds = [],
        $motTestType = MotTestTypeCode::NORMAL_TEST,
        $originalMotTest = null
    )
    {
        $tester = $this->getTestTester($roleText);

        $motTest = new MotTest();
        $motTest
            ->setTester($tester)
            ->setMotTestType((new MotTestType())->setCode($motTestType));

        $motTestArray = $this->getMotTestArray();
        $motTestArray['pendingDetails']['currentSubmissionStatus'] = $expectedStatus;
        if ($odometerValue) {
            $motTest->setOdometerReading(
                OdometerReading::create()
                    ->setUnit(OdometerUnit::MILES)->setValue($odometerValue)
            );
        }
        if ($originalMotTest) {
            $motTest->setMotTestIdOriginal($originalMotTest);
        }
        if ($brakeTestResult instanceof BrakeTestResultClass12) {
            $motTest->setBrakeTestResultClass12($brakeTestResult);
        }
        if ($brakeTestResult instanceof BrakeTestResultClass3AndAbove) {
            $motTest->setBrakeTestResultClass3AndAbove($brakeTestResult);
        }

        foreach ($rfrIds as $rfrId) {
            $rfr = new MotTestReasonForRejection();
            $rfr->setId($rfrId);
            $rfr->setType(ReasonForRejectionTypeName::FAIL);
            $motTest->addMotTestReasonForRejection($rfr);
        }

        $mocks = $this->getMocksForMotTestService();
        $this->mockGetMotTestByTestNumber($this->mockMotTestRepository, $motTest);

        $this->mockMotTestMapper
            ->expects($this->any())
            ->method('mapMotTest')
            ->willReturn($motTestArray);

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $motTestData = $motTestService->getMotTestData(self::MOT_TEST_NUMBER);
        $this->assertEquals($expectedStatus, $motTestData['pendingDetails']['currentSubmissionStatus']);
    }

    public static function pendingStatusIncompleteTestRolesData()
    {
        return [
            [
                MotTestService::PENDING_INCOMPLETE_STATUS,
                SiteBusinessRoleCode::TESTER,
            ],
            [
                MotTestService::PENDING_INCOMPLETE_STATUS,
                Role::VEHICLE_EXAMINER,
            ],
            [
                MotTestService::PENDING_INCOMPLETE_STATUS,
                SiteBusinessRoleCode::TESTER,
                1234,
            ],
            [
                MotTestStatusName::PASSED,
                Role::VEHICLE_EXAMINER,
                999,
            ],
            [
                MotTestStatusName::FAILED,
                Role::VEHICLE_EXAMINER,
                999,
                null,
                [45, 23,]
            ],
            [
                MotTestService::PENDING_INCOMPLETE_STATUS,
                SiteBusinessRoleCode::TESTER,
                2312,
                null,
                [23,]
            ],
            [
                MotTestStatusName::PASSED,
                SiteBusinessRoleCode::TESTER,
                2312,
                new BrakeTestResultClass3AndAbove()
            ],
            [
                MotTestStatusName::FAILED,
                SiteBusinessRoleCode::TESTER,
                2312,
                new BrakeTestResultClass3AndAbove(),
                [312, 5021],
            ],
            [
                MotTestStatusName::PASSED,
                SiteBusinessRoleCode::TESTER,
                23212,
                null,
                [],
                MotTestTypeCode::RE_TEST,
                self::getTestMotRetestWithBrakeTestResults(self::BRAKE_TEST_PASSED)
            ],
            [
                MotTestService::PENDING_INCOMPLETE_STATUS,
                SiteBusinessRoleCode::TESTER,
                212,
                null,
                [],
                MotTestTypeCode::RE_TEST,
                self::getTestMotRetestWithBrakeTestResults(self::BRAKE_TEST_FAILED)
            ],
        ];
    }

    /**
     * This is the data provided for the test
     *
     * @dataProvider pendingMotTestStatusVehicleClassData
     */
    public function testGetPendingMotTestStatusPassed($isClassBelow3)
    {
        $motTest = self::getTestMotTestEntity();
        $tester = $this->getTestTester();
        $motTest->setTester($tester);

        $motTest->setOdometerReading(
            OdometerReading::create()->setUnit(OdometerUnit::MILES)
                ->setValue(1000)
        );
        $motTestArray = $this->getMotTestArray();
        $motTestArray['pendingDetails']['currentSubmissionStatus'] = 'PASSED';

        if ($isClassBelow3) {
            $motTest->setBrakeTestResultClass3AndAbove(new BrakeTestResultClass3AndAbove());
        } else {
            $motTest->setBrakeTestResultClass12(new BrakeTestResultClass12());
        }

        $mocks = $this->getMocksForMotTestService();

        $this->mockGetMotTestByTestNumber($this->mockMotTestRepository, $motTest);
        $this->mockMotTestMapper
            ->expects($this->any())
            ->method('mapMotTest')
            ->willReturn($motTestArray);

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $motTestData = $motTestService->getMotTestData(self::MOT_TEST_NUMBER);
        $this->assertEquals('PASSED', $motTestData['pendingDetails']['currentSubmissionStatus']);
    }

    public static function getTestMotRetestWithBrakeTestResults($brakeTestPassed)
    {
        $motTest = self::getTestMotTestEntity();
        $brakeTestResult = new BrakeTestResultClass3AndAbove();
        $brakeTestResult->setGeneralPass($brakeTestPassed);
        $motTest->setBrakeTestResultClass3AndAbove($brakeTestResult);

        return $motTest;
    }

    public static function pendingMotTestStatusVehicleClassData()
    {
        return [[self::VEHICLE_CLASS_BELOW_3], [self::VEHICLE_CLASS_4_OR_ABOVE]];
    }

    public function testGetPendingMotTestStatusFailed()
    {
        $motTestReasonForRejection = new MotTestReasonForRejection();
        $motTestReasonForRejection->setType('FAIL');

        $motTest = self::getTestMotTestEntity();
        $tester = $this->getTestTester();
        $motTest->setTester($tester);

        $motTest->setOdometerReading(
            OdometerReading::create()->setValue(1000)
                ->setUnit(OdometerUnit::MILES)
        )
            ->setBrakeTestResultClass3AndAbove(new BrakeTestResultClass3AndAbove())
            ->addMotTestReasonForRejection($motTestReasonForRejection);

        $motTestArray = $this->getMotTestArray();
        $motTestArray['pendingDetails']['currentSubmissionStatus'] = MotTestStatusName::FAILED;

        $mocks = $this->getMocksForMotTestService();

        $this->mockGetMotTestByTestNumber($this->mockMotTestRepository, $motTest);

        $this->mockMotTestMapper
            ->expects($this->any())
            ->method('mapMotTest')
            ->willReturn($motTestArray);

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $motTestData = $motTestService->getMotTestData(self::MOT_TEST_NUMBER);
        $this->assertEquals(
            MotTestStatusName::FAILED,
            $motTestData['pendingDetails']['currentSubmissionStatus']
        );
    }

    public function testGetMotTestsByVrm()
    {
        $mocks = $this->getMocksForMotTestService();
        $vrm = 'AB11 7GB';
        $maxResults = 5;
        $motTestCollection = $this->getMotTestEntityCollection(3);

        $vehicle = (new Vehicle())->setId(1);
        $repositories = [
            MotTest::class => $this->mockMotTestRepository,
            Vehicle::class => $this->mockVehicleLookup($vehicle, 'registration', $vrm)
        ];

        $this->mockEntityManager($repositories);

        $this->mockMotTestRepository
            ->expects($this->once())
            ->method('getLatestMotTestsByVehicleId')
            ->with($vehicle->getId(), $maxResults)
            ->willReturn($motTestCollection);

        $this->mockMotTestMapper
            ->expects($this->any())
            ->method('mapMotTest')
            ->willReturn($this->getMotTestArray());

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);
        $motTestDataList = $motTestService->getMotTestsByVrm($vrm, $maxResults);
        $this->assertInternalType('array', $motTestDataList);
        foreach ($motTestDataList as $motTest) {
            $this->checkExtractedMotTestData($motTest);
        }
    }

    /**
     * @param Site $vehicleTestingStation
     */
    protected function addOrg($vehicleTestingStation)
    {
        $org = new Organisation();
        $org->setSlotBalance(MotTestServiceTest::SLOTS_COUNT_START);
        $org->setId(9);
        $vehicleTestingStation->setOrganisation($org);
    }

    protected function getMotTestEntityCollection($size)
    {
        $collection = [];
        for ($i = 1; $i <= $size; $i++) {
            $collection[] = $this->getMotTestEntity($i);
        }

        return $collection;
    }

    protected function getMotTestArray()
    {
        $motTestData = [
            'tester' => '',
            'vehicle' => '',
            'vehicleTestingStation' => '',
            'reasonsForRejection' => '',
            'pendingDetails' => [
                'currentSubmissionStatus' => ''
            ],
        ];

        return $motTestData;
    }

    protected function getMotTestEntity($id)
    {
        $make = new Make();
        $make->setId(1)
            ->setCode('test');

        $model = new Model();
        $model->setCode('Vectra')
            ->setMake($make);

        $modelDetail = new ModelDetail();
        $modelDetail->setModel($model)
            ->setVehicleClass(new VehicleClass(VehicleClassCode::CLASS_4));

        $tester = new Person();
        $tester->setUsername('testUsername');
        $testType = (new MotTestType())->setCode(MotTestTypeCode::NORMAL_TEST);
        $vehicle = new Vehicle();
        $vehicle->setModelDetail($modelDetail);
        $site = new Site();
        $contactDetail = (new ContactDetail())
            ->setAddress(
                (new Address())
                    ->setAddressLine1("Johns Garage")
            );

        $site->setContact($contactDetail, (new SiteContactType()));

        $this->addOrg($site);
        $brakeTestResult = new BrakeTestResultClass3AndAbove();
        $brakeTestResultClass12 = new BrakeTestResultClass12();
        $motRfrAdvisory = MotTestReasonForRejectionTest::getTestMotTestReasonForRejection('ADVISORY');
        $motTest = new MotTest();
        $startedDate = new \DateTime;

        $motTest
            ->setId($id)
            ->setTester($tester)
            ->setMotTestType($testType)
            ->setVehicle($vehicle)
            ->setVehicleTestingStation($site)
            ->setBrakeTestResultClass3AndAbove($brakeTestResult)
            ->setBrakeTestResultClass12($brakeTestResultClass12)
            ->addMotTestReasonForRejection($motRfrAdvisory)
            ->setStartedDate($startedDate)
            ->setStatus($this->createMotTestActiveStatus());

        return $motTest;
    }

    /**
     * Checks the test output from extract to see if it looks reasonable.
     * Because the hydrator object is mocked we only see FK keys and almost no data.
     *
     * @param array $motTestData
     */
    protected function checkExtractedMotTestData($motTestData)
    {
        $this->assertInternalType('array', $motTestData);
        $this->assertArrayHasKey('tester', $motTestData);
        $this->assertArrayHasKey('vehicle', $motTestData);
        $this->assertArrayHasKey('vehicleTestingStation', $motTestData);
        $this->assertArrayHasKey('reasonsForRejection', $motTestData);
    }

    private function mockVehicleLookup($vehicle, $vehicleKey, $vehicleValue)
    {
        $mockVehicleRepository = $this->getMockRepository();
        $mockVehicleRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([$vehicleKey => $vehicleValue])
            ->willReturn($vehicle);

        return $mockVehicleRepository;
    }

    /**
     * Test Get Additional Snapshot Data (Without test station)
     */
    public function testGetAdditionalSnapshotDataWithoutTestStation()
    {
        $mocks = $this->getMocksForMotTestService();
        $vehicleTestStation = null;
        $vehicleId = 1;
        $vehicle = (new Vehicle())->setId($vehicleId);

        $readings = [
            [
                'issuedDate' => '2014-01-01',
                'value' => '10000',
                'unit' => 'mi',
                'resultType' => 'OK'
            ]
        ];
        $expected = [
            'OdometerReadings' => (new OdometerReadingMapper())->manyToDtoFromArray($readings),
        ];

        $motTestMock = $this->getMock(MotTest::class);
        $this->mockMethod($motTestMock, 'getVehicleTestingStation', $this->once(), $vehicleTestStation);
        $this->mockMethod($motTestMock, 'getVehicle', $this->once(), $vehicle);

        $this->mockMethod(
            $this->mockMotTestRepository, 'getMotTestByNumber', $this->once(), $motTestMock, self::MOT_TEST_NUMBER
        );
        $this->mockMethod(
            $this->mockMotTestRepository, 'getOdometerHistoryForVehicleId', $this->once(), $readings, $vehicleId
        );

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $result = $motTestService->getAdditionalSnapshotData(self::MOT_TEST_NUMBER);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test Get Additional Snapshot Data (With test station)
     */
    public function testGetAdditionalSnapshotDataWithTestStation()
    {
        $mocks = $this->getMocksForMotTestService();

        $readings = [
            [
                'issuedDate' => '2014-01-01',
                'value' => '10000',
                'unit' => 'mi',
                'resultType' => 'OK'
            ]
        ];
        $expected = [
            'TestStationAddress' => null,
            'OdometerReadings' => (new OdometerReadingMapper())->manyToDtoFromArray($readings),
        ];

        $vehicleId = 1;
        $motTest = self::getTestMotTestEntity();
        $motTest->getVehicle()->setId($vehicleId);
        $motTestStatus = new MotTestStatus();
        $motTestStatus->setName("PASSED");
        $motTest->setStatus($motTestStatus);

        $this->mockMethod(
            $this->mockMotTestRepository, 'getMotTestByNumber', $this->once(), $motTest, self::MOT_TEST_NUMBER
        );
        $this->mockMethod(
            $this->mockMotTestRepository, 'getOdometerHistoryForVehicleId', $this->once(), $readings, $vehicleId
        );

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $result = $motTestService->getAdditionalSnapshotData(self::MOT_TEST_NUMBER);

        $this->assertEquals($expected, $result);
    }

    public function testGetAdditionalSnapshotData_givenMotTestStatusAsAborted_ShouldNotRetrievePreviousOdometerReading()
    {
        $mocks = $this->getMocksForMotTestService();

        $expected = [
            'TestStationAddress' => null,
        ];

        $vehicleId = 1;
        $motTest = self::getTestMotTestEntity();
        $motTest->getVehicle()->setId($vehicleId);
        $motTestStatus = new MotTestStatus();
        $motTestStatus->setName("ABORTED");
        $motTest->setStatus($motTestStatus);

        $this->mockMethod(
            $this->mockMotTestRepository, 'getMotTestByNumber', $this->once(), $motTest, self::MOT_TEST_NUMBER
        );

        $this->mockMethod($this->mockMotTestRepository, 'getOdometerHistoryForVehicleId', $this->never());

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $result = $motTestService->getAdditionalSnapshotData(self::MOT_TEST_NUMBER);

        $this->assertEquals($expected, $result);

    }

    public function testGetAdditionalSnapshotData_givenMotTestStatusAsAbandoned_ShouldNotRetrievePreviousOdometerReading()
    {
        $mocks = $this->getMocksForMotTestService();

        $expected = [
            'TestStationAddress' => null,
        ];

        $vehicleId = 1;
        $motTest = self::getTestMotTestEntity();
        $motTest->getVehicle()->setId($vehicleId);
        $motTestStatus = new MotTestStatus();
        $motTestStatus->setName("ABANDONED");
        $motTest->setStatus($motTestStatus);

        $this->mockMethod(
            $this->mockMotTestRepository, 'getMotTestByNumber', $this->once(), $motTest, self::MOT_TEST_NUMBER
        );

        $this->mockMethod($this->mockMotTestRepository, 'getOdometerHistoryForVehicleId', $this->never());

        $motTestService = $this->constructMotTestServiceWithMocks($mocks);

        $result = $motTestService->getAdditionalSnapshotData(self::MOT_TEST_NUMBER);

        $this->assertEquals($expected, $result);
    }

    public function testGetCertificateIdsWithOneExpectedCertificate()
    {
        $motTestDto = (new MotTestDto())
            ->setDocument(10);

        /** @var MotTestService $service */
        $service = XMock::of(MotTestService::class, ['getMotTestData']);

        $this->assertEquals([10], $service->getCertificateIds($motTestDto));
    }

    public function testGetCertificateIdsWithTwoExpectedCertificatesReturnsCorrectOrder()
    {
        /** @var MotTestService|PHPUnit_Framework_MockObject_MockObject $service */
        $service = XMock::of(MotTestService::class, ['getMotTestData']);

        $prsMotTestNumber = 9999;

        $motTestDto = (new MotTestDto())
            ->setDocument(10)
            ->setTestType(
                (new MotTestTypeDto())->setCode(MotTestTypeCode::NORMAL_TEST)
            )
            ->setPrsMotTestNumber($prsMotTestNumber);

        $prsMotTestDto = (new MotTestDto())
            ->setMotTestNumber($prsMotTestNumber)
            ->setDocument(20);

        $service->expects($this->once())
            ->method('getMotTestData')
            ->with($prsMotTestNumber)
            ->willReturn($prsMotTestDto);

        $this->assertEquals([20, 10], $service->getCertificateIds($motTestDto));
    }

    public function testGetReplacementCertificateExists()
    {
        $mocks = $this->getMocksForMotTestService();
        $service = $this->constructMotTestServiceWithMocks($mocks);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockRepository */
        $mockRepository = $this->getMockRepository();

        $this->mockEntityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with(\DvsaEntities\Entity\CertificateReplacement::class)
            ->willReturn($mockRepository);

        $mockRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['motTest' => 1])
            ->willReturn('fake result');

        $this->mockEntityManager();
        $this->assertEquals('fake result', $service->getReplacementCertificate(1));
    }

    public function testGetReplacementCertificateReturnsFalseWhenNotExists()
    {
        $mocks = $this->getMocksForMotTestService();
        $service = $this->constructMotTestServiceWithMocks($mocks);

        /** @var PHPUnit_Framework_MockObject_MockObject $mockRepository */
        $mockRepository = $this->getMockRepository();

        $this->mockEntityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with(CertificateReplacement::class)
            ->willReturn($mockRepository);

        $mockRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['motTest' => 1])
            ->willReturn(null);

        $this->assertNull($service->getReplacementCertificate(1));
    }

    private function mockGetMotTestByTestNumber(
        PHPUnit_Framework_MockObject_MockObject $mockTestRepository,
        $expectedResult,
        $motTestNumber = self::MOT_TEST_NUMBER
    )
    {
        $mockTestRepository->expects($this->any())
            ->method('getMotTestByNumber')
            ->with($motTestNumber)
            ->willReturn($expectedResult);
    }

    /**
     * @dataProvider dataProviderTestPrintCertCheckIfUserHasPermissionAtSite
     */
    public function testPrintCertCheckIfUserHasPermissionAtSite($isGrantedAtSite, $isMotTestOwner)
    {
        $siteId = 8888;

        $motTestDto = new MotTestDto();
        $motTestDto->setVehicleTestingStation(
            ['id' => $siteId]
        );

        //  --  mock    --
        $this->getMocksForMotTestService();

        $this->mockAuthService = XMock::of(AuthorisationServiceInterface::class);
        $this->mockIsGrantedAtSite($this->mockAuthService, [PermissionAtSite::CERTIFICATE_PRINT], $siteId);

        $this->mockReadMotTestAssertion->expects($this->any())
            ->method('isMotTestOwnerForDto')
            ->willReturn($isMotTestOwner);

        $service = $this->constructMotTestServiceWithMocks();

        //  --  call & check    --
        $this->assertEquals($isMotTestOwner, $service->canPrintCertificateForMotTest($motTestDto));
    }


    public function dataProviderTestPrintCertCheckIfUserHasPermissionAtSite()
    {
        return [
            [true, true],
            [false, true],
        ];
    }

    /**
     * @dataProvider dataProviderTestIsTestInProgressForVehicle
     */
    public function testIsTestInProgressForVehicle($mocks, $expect)
    {
        //  --  mock    --
        $this->getMocksForMotTestService(null, true);

        if ($mocks !== null) {
            foreach ($mocks as $mock) {
                $invocation = ArrayUtils::tryGet($mock, 'invocation', $this->once());
                $params = ArrayUtils::tryGet($mock, 'params', null);

                $this->mockMethod(
                    $this->{$mock['class']}, $mock['method'], $invocation, $mock['result'], $params
                );
            }
        }

        //  --  set expected exception  --
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message']);
        }

        //  --  call    --
        $motTestService = $this->constructMotTestServiceWithMocks();
        $actual = $motTestService->isTestInProgressForVehicle(self::VEHICLE_ID);

        if (!empty($expect['result'])) {
            $this->assertEquals($expect['result'], $actual);
        }
    }

    public function dataProviderTestIsTestInProgressForVehicle()
    {
        $motTest = new MotTest();
        $motTest->setMotTestType(new MotTestType());

        return [
            [
                'mocks' => [
                    [
                        'class' => 'mockMotTestRepository',
                        'method' => 'findInProgressTestForVehicle',
                        'params' => [self::VEHICLE_ID],
                        'result' => null,
                    ],
                ],
                'expect' => [
                    'result' => false,
                ],
            ],
            [
                'mocks' => [
                    [
                        'class' => 'mockMotTestRepository',
                        'method' => 'findInProgressTestForVehicle',
                        'params' => [self::VEHICLE_ID],
                        'result' => $motTest,
                    ],
                    [
                        'class' => 'mockReadMotTestAssertion',
                        'method' => 'assertGranted',
                        'params' => [$motTest],
                        'result' => null,
                    ],
                ],
                'expect' => [
                    'result' => true,
                ],
            ],
            [
                'mocks' => [
                    [
                        'class' => 'mockMotTestRepository',
                        'method' => 'findInProgressTestForVehicle',
                        'params' => [self::VEHICLE_ID],
                        'result' => $motTest,
                    ],
                    [
                        'class' => 'mockReadMotTestAssertion',
                        'method' => 'assertGranted',
                        'params' => [$motTest],
                        'result' => new UnauthorisedException('unit error message'),
                    ],
                ],
                'expect' => [
                    'exception' => [
                        'class' => UnauthorisedException::class,
                        'message' => 'unit error message',
                    ],
                ],
            ],
        ];
    }

    private function createMotTestActiveStatus()
    {
        $status = XMock::of(MotTestStatus::class);
        $this->mockMethod($status, 'getName', null, MotTestStatusName::ACTIVE);

        return $status;
    }

    private function mockEntityManager(array $repositories = [])
    {
        $callback = function ($entityName) use ($repositories) {
            if (isset($repositories[$entityName])) {
                return $repositories[$entityName];
            }

            return null;
        };

        $this
            ->mockEntityManager
            ->expects($this->any())
            ->method("getRepository")
            ->willReturnCallback($callback);
    }
}
