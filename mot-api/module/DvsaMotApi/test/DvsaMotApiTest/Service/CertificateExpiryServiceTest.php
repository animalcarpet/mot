<?php

namespace DvsaMotApiTest\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Date\DateUtils;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Repository\ConfigurationRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\VehicleRepository;
use DvsaMotApi\Service\CertificateExpiryService;
use DvsaMotApiTest\Service\Fixtures\CsvFileIterator;
use DvsaMotApi\Service\MotTestDate;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class CertificateExpiryServiceTest.
 */
class CertificateExpiryServiceTest extends AbstractServiceTestCase
{
    const TEST_CALENDAR_MONTHS_ALLOWED_TO_POST_DATE = 1;
    const YEARS_BEFORE_FIRST_TEST_IS_DUE = 3;

    const VEHICLE_ID = '42';

    /**
     * @var AuthorisationServiceInterface|MockObj
     */
    protected $authorisationService;

    /**
     * @var MotTestRepository|MockObj
     */
    protected $motTestRepository;

    /**
     * @var VehicleRepository|MockObj
     */
    protected $vehicleRepository;

    /**
     * @var ConfigurationRepository|MockObj
     */
    protected $configurationRepository;

    public function setUp()
    {
        $this->configurationRepository = \DvsaCommonTest\TestUtils\XMock::of(
            ConfigurationRepository::class,
            ['getValue']
        );

        $this->motTestRepository = XMock::of(MotTestRepository::class);
        $this->vehicleRepository = XMock::of(VehicleRepository::class, ['find']);
        $this->authorisationService = $this->getMockAuthorizationService();
    }

    public function testCheckVehicleWithNoTestHavingExpiryDate()
    {
        //given
        $date = DateUtils::toDate('2012-05-30');

        $this->setupMotTestRepositoryMockReturnsLastCertificateExpiryDate(null);

        $vehicle = $this->createVehicle();
        $vehicle->setFirstUsedDate($date);
        $vehicle->setManufactureDate($date);
        $vehicle->setFirstRegistrationDate($date);

        $this->setupVehicleRepositoryMockReturnsVehicle($vehicle);

        $certificateExpiryService = new CertificateExpiryService(
            new TestDateTimeHolder($date),
            $this->motTestRepository,
            $this->vehicleRepository,
            $this->configurationRepository,
            $this->authorisationService
        );

        // when
        $checkExpiryResults = $certificateExpiryService->getExpiryDetailsForVehicle(self::VEHICLE_ID);

        // then
        $this->assertEquals(false, $checkExpiryResults['previousCertificateExists']);
        $this->assertEquals('2015-05-29', $checkExpiryResults['expiryDate']);
        $this->assertEquals('2015-04-30', $checkExpiryResults['earliestTestDateForPostdatingExpiryDate']);
        $this->assertEquals(true, $checkExpiryResults['isEarlierThanTestDateLimit']);
    }

    public function testCheckVehicleWhenPassingTestingDate()
    {
        //given
        $dateInvalid = DateUtils::toDate('2012-05-30');
        $date = DateUtils::toDate('2012-05-30');

        $this->setupMotTestRepositoryMockReturnsLastCertificateExpiryDate(null);

        $vehicle = $this->createVehicle();
        $vehicle->setFirstUsedDate($date);
        $vehicle->setManufactureDate($date);
        $vehicle->setFirstRegistrationDate($date);
        $this->setupVehicleRepositoryMockReturnsVehicle($vehicle);

        $certificateExpiryService = new CertificateExpiryService(
            new TestDateTimeHolder($dateInvalid),
            $this->motTestRepository,
            $this->vehicleRepository,
            $this->configurationRepository,
            $this->authorisationService
        );

        // when
        // vehicleId = 3
        $dateTimeHolder = new TestDateTimeHolder($date);
        $checkExpiryResults = $certificateExpiryService->getExpiryDetailsForVehicle(
            self::VEHICLE_ID,
            $dateTimeHolder
        );

        // then
        $this->assertEquals(false, $checkExpiryResults['previousCertificateExists']);
        $this->assertEquals('2015-05-29', $checkExpiryResults['expiryDate']);
        $this->assertEquals('2015-04-30', $checkExpiryResults['earliestTestDateForPostdatingExpiryDate']);
        $this->assertEquals(true, $checkExpiryResults['isEarlierThanTestDateLimit']);
    }

    public function testCheckVehicleWithNoFirstUsedDate()
    {
        //given
        $dateInvalid = DateUtils::toDate('2012-05-29');
        $date = DateUtils::toDate('2012-05-30');

        $this->setupMotTestRepositoryMockReturnsLastCertificateExpiryDate(null);

        // class4, registered new at first reg
        $vehicle = $this->createVehicle(1, 4, true);
        $vehicle->setFirstUsedDate($date);
        $vehicle->setManufactureDate($date);
        $vehicle->setFirstRegistrationDate($date);

        $this->setupVehicleRepositoryMockReturnsVehicle($vehicle);

        $certificateExpiryService = new CertificateExpiryService(
            new TestDateTimeHolder($dateInvalid),
            $this->motTestRepository,
            $this->vehicleRepository,
            $this->configurationRepository,
            $this->authorisationService
        );

        $dateTimeHolder = new TestDateTimeHolder($date);
        // when
        // vehicleId = 3
        $checkExpiryResults = $certificateExpiryService->getExpiryDetailsForVehicle(
            self::VEHICLE_ID,
            $dateTimeHolder
        );

        $this->assertFalse($checkExpiryResults['previousCertificateExists']);
    }

    public function testCheckVehicleWithTodayAfterEarliestDateAllowed()
    {
        //given
        $currentDate = (new \DateTime())->setDate(2014, 4, 20);
        $expiryDate = (new \DateTime())->setDate(2014, 5, 10);
        $this->setupMotTestRepositoryMockReturnsLastCertificateExpiryDate($expiryDate);

        $certificateExpiryService = new CertificateExpiryService(
            new TestDateTimeHolder($currentDate),
            $this->motTestRepository,
            $this->vehicleRepository,
            $this->configurationRepository,
            $this->authorisationService
        );

        // when
        $checkExpiryResults = $certificateExpiryService->getExpiryDetailsForVehicle(1);

        // then
        $this->assertEquals(true, $checkExpiryResults['previousCertificateExists']);
        $this->assertEquals('2014-05-10', $checkExpiryResults['expiryDate']);
        $this->assertEquals('2014-04-11', $checkExpiryResults['earliestTestDateForPostdatingExpiryDate']);
        $this->assertEquals(false, $checkExpiryResults['isEarlierThanTestDateLimit']);
    }

    public function testCheckVehicleWithTodayBeforeEarliestDateAllowed()
    {

        //given
        $currentDate = (new \DateTime())->setDate(2014, 4, 2);
        $expiryDate = (new \DateTime())->setDate(2014, 5, 10);
        $this->setupMotTestRepositoryMockReturnsLastCertificateExpiryDate($expiryDate);

        $certificateExpiryService = new CertificateExpiryService(
            new TestDateTimeHolder($currentDate),
            $this->motTestRepository,
            $this->vehicleRepository,
            $this->configurationRepository,
            $this->authorisationService);

        // when
        $checkExpiryResults = $certificateExpiryService->getExpiryDetailsForVehicle(1);

        // then
        $this->assertEquals(true, $checkExpiryResults['previousCertificateExists']);
        $this->assertEquals('2014-05-10', $checkExpiryResults['expiryDate']);
        $this->assertEquals('2014-04-11', $checkExpiryResults['earliestTestDateForPostdatingExpiryDate']);
        $this->assertEquals(true, $checkExpiryResults['isEarlierThanTestDateLimit']);
    }

    /**
     * @param string $vehicleClass         the type of vehicle
     * @param string $newAtFirstReg        the word yes or no
     * @param string $dateFirstUsed        the date the vehicle was first used as YYYY-MM-DD, like all the CSV dates
     * @param string $dateRegistered       the date the vehicle was registered
     * @param string $dateManufactured     the date the vehicle made
     * @param string $dateFirstMotDue      when the first MOT is due with respect to vehicle class
     * @param string $preservationDate     the preservation date start period
     * @param string $dateOfMotTest        the date the test was performed for the test case
     * @param string $expiryDate           the expected expiry of the MOT test
     * @param string $testPreservationDate the expectec preservation of the NEXT mot test
     *
     * @SuppressWarnings(unused)
     * @dataProvider dpTestExpiryDate2
     */
    public function testClassAwareExpiryDate(
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
    ) {
        $certificateExpiryService = new CertificateExpiryService(
            new TestDateTimeHolder(new \DateTime($dateOfMotTest)),
            $this->motTestRepository,
            $this->vehicleRepository,
            $this->configurationRepository,
            $this->authorisationService);

        $this->configurationRepository->expects($this->any())
            ->method('getValue')
            ->willReturnCallback(
                function ($key) {
                    switch ($key) {
                        case CertificateExpiryService::YEARS_BEFORE_FIRST_TEST_IS_DUE:
                            return 3;
                        case CertificateExpiryService::YEARS_BEFORE_FIRST_TEST_IS_DUE_CLASS_5:
                            return 1;
                    }
                }
            );

        $vehicle = $this->createVehicle(1, $vehicleClass);
        $this->setupVehicleRepositoryMockReturnsVehicle($vehicle);

        $calculatedExpiryDate = $certificateExpiryService->getInitialClassAwareExpiryDate(
            $vehicleClass,
            'Yes' === $newAtFirstReg,
            new \DateTime($dateManufactured),
            new \DateTime($dateRegistered)
        );
        $this->assertEquals(new \DateTime($dateFirstMotDue), $calculatedExpiryDate);

        $nextPreservationDate = MotTestDate::preservationDate(new \DateTime($dateFirstMotDue));
        $this->assertEquals(new \DateTime($preservationDate), $nextPreservationDate);
    }

    public function dpTestExpiryDate2()
    {
        return new CsvFileIterator(__DIR__.'/Fixtures/10110.csv');
    }

    protected function setupMotTestRepositoryMockReturnsLastCertificateExpiryDate($date)
    {
        $this->motTestRepository
            ->expects($this->any())
            ->method('findLastCertificateExpiryDate')
            ->withAnyParameters()
            ->will($this->returnValue($date));
    }

    protected function setupVehicleRepositoryMockReturnsVehicle($vehicle)
    {
        $this->vehicleRepository
            ->expects($this->any())
            ->method('find')
            ->withAnyParameters()
            ->will($this->returnValue($vehicle));
    }

    protected function setupConfigurationRepositoryMockFindValuePostDateMonths()
    {
        $this->configurationRepository
            ->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(self::TEST_CALENDAR_MONTHS_ALLOWED_TO_POST_DATE));
    }

    protected function setupConfigurationRepositoryMockFindValueForNoTestHavingExpiryDate()
    {
        $this->configurationRepository->expects($this->at(0))
            ->method('getValue')
            ->will($this->returnValue(self::YEARS_BEFORE_FIRST_TEST_IS_DUE));
    }

    protected function createVehicle($id = self::VEHICLE_ID, $vehicleClassCode = 4, $newAtFirstReg = false)
    {
        $vehicleClass = new VehicleClass();
        $vehicleClass->setCode($vehicleClassCode);

        $modelDetail = new ModelDetail();
        $modelDetail->setVehicleClass($vehicleClass);

        $vehicle = new Vehicle();
        $vehicle->setId($id);
        $vehicle->setVin('TEST-VIN-001');
        $vehicle->setNewAtFirstReg($newAtFirstReg);
        $vehicle->setModelDetail($modelDetail);

        return $vehicle;
    }
}
