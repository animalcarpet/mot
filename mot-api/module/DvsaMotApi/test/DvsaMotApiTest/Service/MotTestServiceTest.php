<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Service;

use DateTime;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaClient\Entity\SitePosition;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Constants\Role;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Time;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Mapper\OdometerReadingMapper;
use DvsaCommonTest\TestUtils\ArgCapture;
use DvsaCommonTest\TestUtils\MockHandler;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\BrakeTestResultClass12;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\EmergencyLog;
use DvsaEntities\Entity\FuelType;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestEmergencyReason;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\ReasonForRejectionType;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Entity\SiteTestingDailySchedule;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntitiesTest\Entity\MotTestReasonForRejectionTest;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApiTest\Factory\MotTestObjectsFactory;
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

    const SITE_ID = 1;
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

        $motTestEmergencyReason = new MotTestEmergencyReason();
        $motTestEmergencyReason->setEmergencyLog(new EmergencyLog());

        $motTest->setMotTestEmergencyReason($motTestEmergencyReason);
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

        $motTestEmergencyReason = new MotTestEmergencyReason();
        $motTestEmergencyReason->setEmergencyLog(new EmergencyLog());

        $motTest->setMotTestEmergencyReason($motTestEmergencyReason);
        $motTest->setStartedDate(new \DateTime('now'));
        $motTest->setIssuedDate((new \DateTime('now'))->add(new \DateInterval('P1D')));
        $testStatus = $this->getMock('\DvsaEntities\Entity\MotTestStatus');

        $testStatus->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(MotTestStatusName::ACTIVE));

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

        $motTestEmergencyReason = new MotTestEmergencyReason();
        $motTestEmergencyReason->setEmergencyLog(new EmergencyLog());

        $motTest->setMotTestEmergencyReason($motTestEmergencyReason);
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

        $motTestEmergencyReason = new MotTestEmergencyReason();
        $motTestEmergencyReason->setEmergencyLog(new EmergencyLog());

        $motTest->setMotTestEmergencyReason($motTestEmergencyReason);
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

        $motTestEmergencyReason = new MotTestEmergencyReason();
        $motTestEmergencyReason->setEmergencyLog(new EmergencyLog());

        $motTest->setMotTestEmergencyReason($motTestEmergencyReason);
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
            $motTest->setOdometerValue($odometerValue);
            $motTest->setOdometerUnit(OdometerUnit::MILES);
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
            $rfr->setType((new ReasonForRejectionType())->setReasonForRejectionType(ReasonForRejectionTypeName::FAIL));
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
        $motTest->setOdometerValue(1000);
        $motTest->setOdometerUnit(OdometerUnit::MILES);

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
        $motTestReasonForRejection->setType((new ReasonForRejectionType())->setReasonForRejectionType('FAIL'));

        $motTest = self::getTestMotTestEntity();
        $tester = $this->getTestTester();
        $motTest->setTester($tester);
        $motTest->setOdometerValue(1000);
        $motTest->setOdometerUnit(OdometerUnit::MILES);
        $motTest->setBrakeTestResultClass3AndAbove(new BrakeTestResultClass3AndAbove());
        $motTest->addMotTestReasonForRejection($motTestReasonForRejection);

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
        $this->mockMethod($motTestMock, 'getVehicle', $this->atLeastOnce(), $vehicle);
        $this->mockMethod($this->mockMysteryShopperHelper, 'isMysteryShopperToggleEnabled', $this->atLeastOnce(), false);

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
     * Test Get Additional Snapshot Data (Without test station)
     */
    public function testGetAdditionalSnapshotDataForMysteryShopperVehicle()
    {
        $mocks = $this->getMocksForMotTestService();
        $vehicleTestStation = null;
        $vehicleId = 1;
        $vehicle = (new Vehicle())->setId($vehicleId);

        $vehicleOdometerHistory = [
            [
                'issuedDate' => '2014-01-01',
                'value' => '10000',
                'unit' => 'mi',
                'resultType' => 'OK'
            ]
        ];

        $expected = [
            'OdometerReadings' => (new OdometerReadingMapper())->manyToDtoFromArray($vehicleOdometerHistory),
        ];

        $motTestMock = $this->getMock(MotTest::class);
        $motTestTypeMock = $this->getMock(MotTestType::class);
        $this->mockMethod($motTestMock, 'getVehicleTestingStation', $this->once(), $vehicleTestStation);
        $this->mockMethod($motTestMock, 'getVehicle', $this->atLeastOnce(), $vehicle);
        $this->mockMethod($motTestMock, 'getMotTestType', $this->atLeastOnce(), $motTestTypeMock);
        $this->mockMethod($motTestTypeMock, 'getCode', $this->atLeastOnce(), MotTestTypeCode::MYSTERY_SHOPPER);
        $this->mockMethod($this->mockMysteryShopperHelper, 'isMysteryShopperToggleEnabled', $this->atLeastOnce(), true);

        $this->mockMethod(
            $this->mockMotTestRepository, 'getMotTestByNumber', $this->once(), $motTestMock, self::MOT_TEST_NUMBER
        );
        $this->mockMethod(
            $this->mockMotTestRepository, 'getOdometerHistoryForVehicleId', $this->once(), $vehicleOdometerHistory, $vehicleId
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



    private function notificationOnTestOutsideOpeningHoursExpected()
    {
        list($site, $person, $startedDate) = [ArgCapture::create(), ArgCapture::create(), ArgCapture::create()];
        $this->mockTestingOutsideOpeningHoursNotificationService->expects($this->once())
            ->method('notify')->with($site(), $person(), $startedDate());

        return [$site, $person, $startedDate];
    }

    private function notificationOnTestOutsideOpeningHoursNotExpected()
    {
        $this->mockTestingOutsideOpeningHoursNotificationService->expects($this->never())
            ->method('notify');
    }


    private function setStartedTestOutsideOpeningHours(MotTest $motTest, $testStartedHour)
    {
        $year = "2016";
        $month = "09";
        $day = "05";

            $startedDate = DateUtils::toDateTimeFromParts($year,$month,$day,$testStartedHour);

        $motTest->setStartedDate($startedDate);

        return $motTest;
    }

    private static function addSiteOpeningHours(MotTest $motTest)
    {
        list($openingTime, $closingTime) = [Time::fromIso8601('08:00:00'), Time::fromIso8601('16:00:00')];
        $weekOpeningHours = [];
        for ($i = 1; $i <= 7; $i++) {
            $dailySchedule = (new SiteTestingDailySchedule())
                ->setOpenTime($openingTime)
                ->setCloseTime($closingTime)
                ->setWeekday($i);
            $weekOpeningHours [] = $dailySchedule;
        }
        $motTest->getVehicleTestingStation()->setSiteTestingSchedule($weekOpeningHours);
    }

    private function setUpForTestUpdateStatusOutsideOpeningHours(MotTest $motTest, $testStartedHour)
    {
        $siteBusinesRole = new SiteBusinessRole();
        $siteBusinesRole->setCode(RoleCode::SITE_MANAGER);
        $siteManager = new Person();
        $siteBusinessRoleMap = (new SiteBusinessRoleMap())->setPerson($siteManager)->setSiteBusinessRole($siteBusinesRole);

        $motTest->getVehicleTestingStation()->setPositions([$siteBusinessRoleMap]);

        MotTestObjectsFactory::addTestAuthorisationForClass(
            $motTest,
            '4',
            AuthorisationForTestingMotStatusCode::QUALIFIED
        );

        self::addSiteOpeningHours($motTest);
        $motTest = $this->setStartedTestOutsideOpeningHours($motTest, $testStartedHour);

        return $motTest;
    }

    /**
     * @return array
     */
    public function dataProviderGivenTestOutsideSiteOpeningHoursShouldNotifyOrNot()
    {
        return [
            ["02", true],
            ["05", true],
            ["11", false],
            ["13", false],
            ["19", true],
            ["21", true],
        ];
    }

    /**
     * @param $testStartedHour
     * @param $shouldNotify
     *
     * @dataProvider dataProviderGivenTestOutsideSiteOpeningHoursShouldNotifyOrNot
     */
    public function testForTestOutsideSiteOpeningHoursShouldNotifyOrNot($testStartedHour, $shouldNotify)
    {
        $mocks = $this->getMocksForMotTestService();
        $service = $this->constructMotTestServiceWithMocks($mocks);
        $this->mockCreateMotTest($this->mockCreateMotTestService, $this->getTestData(), $testStartedHour);

        if ($shouldNotify) {
            $this->notificationOnTestOutsideOpeningHoursExpected();
        } else {
            $this->notificationOnTestOutsideOpeningHoursNotExpected();
        }
        $service->createMotTest($this->getTestData());
    }

    private function mockCreateMotTest(
        PHPUnit_Framework_MockObject_MockObject $mockCreateTestRepository,
        array $data,
        $testStartedHour
    )
    {
        $mockCreateTestRepository
            ->expects($this->any())
            ->method('create')
            ->with($data)
            ->willReturn(
                $this->setUpForTestUpdateStatusOutsideOpeningHours(
                    self::getMotTestEntity('1'),
                    $testStartedHour
                )
            );
    }

    /**
     * @return array
     */
    protected function getTestData()
    {
        return [
            "vehicleId" => self::VEHICLE_ID,
            "vehicleTestingStationId" => self::SITE_ID,
            "primaryColour" => ColourCode::GREY,
            "secondaryColour" => ColourCode::NOT_STATED,
            "fuelTypeId" => FuelTypeCode::PETROL,
            "vehicleClassCode" => VehicleClassCode::CLASS_4,
            "hasRegistration" => true,
            "oneTimePassword" => null
        ];
    }
}
