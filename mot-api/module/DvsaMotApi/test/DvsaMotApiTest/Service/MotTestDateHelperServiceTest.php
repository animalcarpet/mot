<?php

namespace DvsaMotApiTest\Service;

use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\EmergencyLog;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTestEmergencyReason;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\MotTestDate;
use DvsaMotApi\Service\MotTestDateHelperService;
use DvsaMotApi\Service\MotTestStatusService;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApiTest\Factory\MotTestObjectsFactory;
use DvsaMotApiTest\Factory\VehicleObjectsFactory;
use DvsaMotApiTest\Service\Fixtures\CsvFileIterator;

/**
 * Class MotTestDateHelperServiceTest
 *
 * @package DvsaMotApiTest\Service
 */
class MotTestDateHelperServiceTest extends AbstractServiceTestCase
{
    const TEST_ISSUED_DATE = '2014-05-01';
    const TEST_EXPIRY_DATE = '2015-04-30';

    const CURRENT_DATE = '2014-05-01';

    const TEST_STATUS_PASS = 1;
    const TEST_STATUS_PENDING_PASS = 2;
    const TEST_STATUS_FAIL = 3;
    const TEST_STATUS_INCOMPLETE = 4;

    private $currentDateTime;
    private $mockMotTestStatusService;

    public function setUp()
    {
        $this->currentDateTime = DateUtils::toDate(self::CURRENT_DATE);
        $this->mockMotTestStatusService = XMock::of(MotTestStatusService::class, []);
    }

    /**
     * @param int|null $status MotTest status Passed or Pending Passed
     * @param string $issuedDate MotTest issued date, if null take from MotTest
     * @param int $testType MotTest type
     * @param string $expectResult Expected Date
     * @param boolean $emergency Emergency test
     *
     * @dataProvider dataProviderTestIssuedDate
     */
    public function testIssuedDate(
        $status,
        $issuedDate,
        $testType,
        $expectResult,
        $emergency = false
    )
    {
        $issuedDate = $issuedDate ? DateUtils::toDate($issuedDate) : $issuedDate;
        $expectResult = $expectResult ? DateUtils::toDate($expectResult) : null;

        //  --  mock MotTest    --
        $motTest = $this->getMockMotTest($testType, $status);

        if ($emergency === true) {
            $emergencyLog = new EmergencyLog();

            $motTestEmergencyReason = new MotTestEmergencyReason();
            $motTestEmergencyReason->setEmergencyLog($emergencyLog);

            $motTest->setMotTestEmergencyReason($motTestEmergencyReason);
        }

        $pendingStatus = null;
        if ($status === self::TEST_STATUS_PENDING_PASS) {
            $pendingStatus = MotTestStatusName::PASSED;
        } elseif ($status === self::TEST_STATUS_INCOMPLETE) {
            $pendingStatus = MotTestService::PENDING_INCOMPLETE_STATUS;
        }

        //  --  prepare checked service --
        $motTestDateHelper = $this->getMockMotTestDateHelper();

        $result = $motTestDateHelper->getIssuedDate($motTest, $issuedDate, $pendingStatus);

        $this->assertEquals($expectResult, $result);
    }

    public function dataProviderTestIssuedDate()
    {
        $issuedDate = '2014-01-31';

        return [
            [
                'status' => null,
                'issuedDate' => null,
                'testType' => MotTestTypeCode::NORMAL_TEST,
                'expectResult' => self::CURRENT_DATE,
            ],
            [null, $issuedDate, MotTestTypeCode::NORMAL_TEST, $issuedDate],
            [null, $issuedDate, MotTestTypeCode::NON_MOT_TEST, $issuedDate],

            [self::TEST_STATUS_FAIL, null, MotTestTypeCode::NORMAL_TEST, self::CURRENT_DATE],
            [self::TEST_STATUS_FAIL, $issuedDate, MotTestTypeCode::NORMAL_TEST, $issuedDate],

            [self::TEST_STATUS_FAIL, null, MotTestTypeCode::MOT_COMPLIANCE_SURVEY, self::CURRENT_DATE],
            [self::TEST_STATUS_FAIL, null, MotTestTypeCode::TARGETED_REINSPECTION, self::CURRENT_DATE],

            [self::TEST_STATUS_PASS, null, MotTestTypeCode::NORMAL_TEST, self::CURRENT_DATE],
            [self::TEST_STATUS_PENDING_PASS, null, MotTestTypeCode::NORMAL_TEST, self::CURRENT_DATE],
            [self::TEST_STATUS_PENDING_PASS, $issuedDate, MotTestTypeCode::NORMAL_TEST, $issuedDate],

            [self::TEST_STATUS_INCOMPLETE, $issuedDate, MotTestTypeCode::NORMAL_TEST, null],

            [self::TEST_STATUS_PASS, null, MotTestTypeCode::NORMAL_TEST, self::CURRENT_DATE, true],
        ];
    }


    /**
     * This test will show that, with no prior MOT test on record, that the expiry date
     * is a function of the set:
     *     {vehicle class, registered as new, manufacturer date, registration date}
     *
     * @param string $vehicleClass the type of vehicle
     * @param string $newAtFirstReg the word yes or no
     * @param string $dateFirstUsed the date the vehicle was first used as YYYY-MM-DD, like all the CSV dates
     * @param string $dateRegistered the date the vehicle was registered
     * @param string $dateManufactured the date the vehicle made
     * @param string $dateFirstMotDue when the first MOT is due with respect to vehicle class
     * @param string $preservationDate the preservation date start period
     * @param string $dateOfMotTest the date the test was performed for the test case
     * @param string $expiryDate the expected expiry of the MOT test
     * @param string $testPreservationDate the expectec preservation of the NEXT mot test
     * @dataProvider dpTestExpiryDate
     *
     * @SuppressWarnings(unused)
     */
    public function testExpiryDateForPassedTestWithNoPrevious(
        $vehicleClass,
        $newAtFirstReg,
        $dateFirstUsed,
        $dateRegistered,
        $dateManufactured,
        $dateFirstMotDue,
        $preservationDate,
        $dateOfMotTest,
        $expiryDate,
        $testPreservationDate
    )
    {
        /** @var DateTimeHolder $now */
        $now = $this->createDateTimeHolder($dateOfMotTest);
        $motTest = $this->getMockMotTest(MotTestTypeCode::NORMAL_TEST, self::TEST_STATUS_PASS);
        $motTest->setIssuedDate($now->getCurrentDate()); // don't matter for
        $motTest->setExpiryDate($now->getCurrentDate()); // this test!

        // Prime the Vehicle entity from the CSV data
        $vehicleClassObj = new VehicleClass();
        $vehicleClassObj->setCode($vehicleClass);

        $modelDetail = new ModelDetail();
        $modelDetail->setVehicleClass($vehicleClassObj);

        $vehicle = new Vehicle();
        $vehicle->setId(999);
        $vehicle->setModelDetail($modelDetail);
        $vehicle->setNewAtFirstReg('yes' === strtolower($newAtFirstReg));
        $vehicle->setFirstRegistrationDate(new \DateTime($dateRegistered));
        $vehicle->setManufactureDate(new \DateTime($dateManufactured));
        $vehicle->setFirstUsedDate(new \DateTime($dateFirstUsed));

        // Make the MOT repository say there is NO previous test
        $mockMotRepo = $this->getMockRepository(MotTestRepository::class);
        $mockMotRepo->expects($this->once())
            ->method('getLatestMotTestByVehicleIdAndResult')
            ->willReturn(null);

        // Prime the MOT helper and initiate the test
        $motTestDateHelper = new MotTestDateHelperService(
            $now,
            $mockMotRepo,
            $this->mockMotTestStatusService
        );

        $motTest->setVehicle($vehicle);
        $generatedExpiryDate = $motTestDateHelper->getExpiryDate($motTest);

        $this->assertEquals($expiryDate, $generatedExpiryDate->format('Y-m-d'));
    }

    public function dpTestExpiryDate()
    {
        return new CsvFileIterator(__DIR__ . '/Fixtures/10110.csv');
    }

    /**
     * This test shows that with a previous *passed* MOT on record, that the expiry date is
     * a function of the issued date of that test if the test date is outside of the preservation
     * date window.
     *
     * @param $vehicleClass ,
     * @param $newAtFirstReg ,
     * @param $firstUseDate ,
     * @param $firstRegDate ,
     * @param $manuDate ,
     * @param $previousTestDate ,
     * @param $nowDate ,
     * @param $expectedExpiryDate
     *
     * @throws \Exception
     *
     * @dataProvider dpTestExpiryDateWithPrevious
     */
    public function testExpiryDateWithPreviousPassedTestOnRecord(
        $vehicleClassCode,
        $newAtFirstReg,
        $firstUseDate,
        $firstRegDate,
        $manuDate,
        $previousTestDate,
        $preservationDate,
        $nowDate,
        $expectedExpiryDate,
        $testPreservationDate
    )
    {
        /** @var DateTimeHolder $now */
        $now = $this->createDateTimeHolder($nowDate);

        // Prime the Vehicle entity from the CSV data
        $vehicleClass = new VehicleClass();
        $vehicleClass->setCode($vehicleClassCode);

        $modelDetail = new ModelDetail();
        $modelDetail->setVehicleClass($vehicleClass);

        $vehicle = new Vehicle();
        $vehicle->setId(999);
        $vehicle->setModelDetail($modelDetail);

        // ensure this date is used for the calculation!
        $vehicle->setNewAtFirstReg('yes' === strtolower($newAtFirstReg));
        $vehicle->setFirstRegistrationDate(new \DateTime($firstRegDate));
        $vehicle->setManufactureDate(new \DateTime($manuDate));
        $vehicle->setFirstUsedDate(new \DateTime($firstUseDate));

        // Set up CURRENT Mot test...
        $motTest = $this->getMockMotTest(MotTestTypeCode::NORMAL_TEST, self::TEST_STATUS_PASS);
        $motTest->setIssuedDate($now->getCurrentDate()); // don't matter for
        $motTest->setExpiryDate($now->getCurrentDate()); // this test!
        $motTest->setVehicle($vehicle);

        // Set up the PREVIOUS Mot test...
        $motTestPrevious = $this->getMockMotTest(MotTestTypeCode::NORMAL_TEST, self::TEST_STATUS_PASS);
        $previousExpiryDate = new \DateTime($previousTestDate);
        $motTestPrevious->setExpiryDate($previousExpiryDate);
        $mockMotRepo = $this->getMockRepository(MotTestRepository::class);
        $mockMotRepo->expects($this->once())
            ->method('getLatestMotTestByVehicleIdAndResult')
            ->willReturn($motTestPrevious);

        // Prime the MOT helper and initiate the test
        $motTestDateHelper = new MotTestDateHelperService($now, $mockMotRepo, $this->mockMotTestStatusService);
        $generatedExpiryDate = $motTestDateHelper->getExpiryDate($motTest);

        $this->assertEquals(new \DateTime($expectedExpiryDate), $generatedExpiryDate);
    }

    public function dpTestExpiryDateWithPrevious()
    {
        return new CsvFileIterator(__DIR__ . '/Fixtures/10110.csv');
    }

    /**
     * @param $id
     * @param $vehicleClass
     * @param bool $regAtNew
     * @return Vehicle
     */
    protected function createVehicle($id, $vehicleClass, $regAtNew = true)
    {
        $vehicleClassObj = new VehicleClass();
        $vehicleClassObj->setCode($vehicleClass);

        $vehicleDate = new \DateTime();

        $vehicle = new Vehicle();
        $vehicle->setId($id);
        $vehicle->setVehicleClass($vehicleClassObj);
        $vehicle->setNewAtFirstReg($regAtNew);
        $vehicle->setFirstRegistrationDate($vehicleDate);
        $vehicle->setManufactureDate($vehicleDate);

        return $vehicle;
    }

    private function getMockMotTestDateHelper($currentDateTime = null)
    {
        if (!($currentDateTime instanceof \DateTime)) {
            $currentDateTime = $this->currentDateTime;
        }

        $motTestDateHelper = new MotTestDateHelperService(
            new DateTimeHolder(),
            $this->getMockRepository(MotTestRepository::class),
            $this->mockMotTestStatusService
        );

        $this->mockClassField($motTestDateHelper, 'dateTimeHolder', new TestDateTimeHolder($currentDateTime));

        return $motTestDateHelper;
    }

    private function getMockMotTest(
        $testTypeCode = MotTestTypeCode::NORMAL_TEST,
        $status = self::TEST_STATUS_PASS
    )
    {
        $testType = (new \DvsaEntities\Entity\MotTestType())->setCode($testTypeCode);
        $motTest = MotTestObjectsFactory::activeMotTest()
            ->setId(1)
            ->setMotTestType($testType)
            ->setVehicle(VehicleObjectsFactory::vehicle(4))
            ->setCompletedDate($this->currentDateTime)
            ->setStatus($this->createMotTestActiveStatus());

        if ($status == self::TEST_STATUS_PASS) {
            $motTest
                ->setStatus($this->createMotTestPassedStatus())
                ->setIssuedDate(DateUtils::toDate(self::TEST_ISSUED_DATE));
        } elseif ($status == self::TEST_STATUS_FAIL) {
            $motTest
                ->setStatus($this->createMotTestFailedStatus());
        }

        return $motTest;
    }

    private function createMotTestActiveStatus()
    {
        return $this->createMotTestStatus(MotTestStatusName::ACTIVE);
    }

    private function createMotTestPassedStatus()
    {
        return $this->createMotTestStatus(MotTestStatusName::PASSED);
    }

    private function createMotTestFailedStatus()
    {
        return $this->createMotTestStatus(MotTestStatusName::FAILED);
    }

    private function createMotTestStatus($name)
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method("getName")
            ->willReturn($name);

        return $status;
    }

    /**
     * Answers a new DateTimeHolder instance with the given time or anchored
     * to "now" if no value is passed.
     *
     * @param mixed $when
     * @return \PHPUnit_Framework_MockObject_MockObject
     * @throws \Exception
     */
    private function createDateTimeHolder($when = null)
    {
        if (null === $when) {
            $when = new \DateTime();
        } elseif (is_string($when)) {
            $when = new \DateTime($when);
        }

        $dateTimeHolder = XMock::of(DateTimeHolder::class, ['getCurrent', 'getCurrentDate']);
        $dateTimeHolder->expects($this->any())->method('getCurrent')->willReturn($when);
        $dateTimeHolder->expects($this->any())->method('getCurrentDate')
            ->willReturn(DateUtils::cropTime($when));

        return $dateTimeHolder;
    }


    /**
     * @param $expiryDate
     * @param $preservationDate
     *
     * @dataProvider providerPreservationDates
     */
    public function testPreservationDateAwkwardSquad($expiryDate, $preservationDate)
    {
        $pd = MotTestDate::preservationDate(new \DateTime($expiryDate));
        $this->assertEquals($preservationDate, $pd->format('Y-m-d'));
    }

    public function providerPreservationDates()
    {
        return [
            // February non-leap year edge cases, 2002 is not a leap year nor are 2001, 2003
            ['2002-03-31', '2002-03-01'],
            ['2002-03-30', '2002-03-01'],
            ['2002-03-29', '2002-03-01'],
            ['2002-03-28', '2002-03-01'],
            ['2002-03-27', '2002-02-28'],

            // February leap-year edge cases
            ['2004-03-31', '2004-03-01'],
            ['2004-03-30', '2004-03-01'],
            ['2004-03-29', '2004-03-01'],
            ['2004-03-28', '2004-02-29'],
            ['2003-03-27', '2003-02-28'],

            // Jan/Dec boundary cross-overs...
            ['2003-01-01', '2002-12-02'],
            ['2003-01-12', '2002-12-13'],
            ['2003-01-30', '2002-12-31'],

            // 31st day => 1st day of the month
            ['2003-01-31', '2003-01-01'],
            ['2003-03-31', '2003-03-01'],
            ['2003-05-31', '2003-05-01'],
            ['2003-07-31', '2003-07-01'],
            ['2003-08-31', '2003-08-01'],
            ['2003-10-31', '2003-10-01'],
            ['2003-12-31', '2003-12-01'],

            ['2015-08-31', '2015-08-01'],
            ['2015-08-30', '2015-07-31'],
            ['2015-08-29', '2015-07-30'],
            ['2015-08-28', '2015-07-29'],

            ['2015-06-30', '2015-05-31'],
            ['2015-07-30', '2015-07-01'],
            ['2015-07-29', '2015-06-30'],
            ['2015-07-28', '2015-06-29'],
            ['2015-04-30', '2015-03-31'],
        ];
    }
}
