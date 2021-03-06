<?php

namespace DvsaMotApiTest\Service\Mapper;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Common\ColourDto;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Vehicle\FuelTypeDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Vehicle\CountryDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\Vehicle\VehicleParamDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\BodyType;
use DvsaEntities\Entity\BrakeTestResultClass12;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\BrakeTestType;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\CountryOfRegistration;
use DvsaEntities\Entity\FuelType;
use DvsaEntities\Entity\Language;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Entity\PhoneContactType;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\ReasonForRejectionDescription;
use DvsaEntities\Entity\ReasonForRejectionType;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Entity\SiteType;
use DvsaEntities\Entity\TestItemCategoryDescription;
use DvsaEntities\Entity\TestItemSelector;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaMotApi\Formatting\DefectSentenceCaseConverter;
use DvsaMotApi\Service\BrakeTestResultService;
use DvsaMotApi\Service\CertificateExpiryService;
use DvsaMotApi\Service\Mapper\MotTestMapper;
use DvsaMotApi\Service\MotTestDateHelperService;
use DvsaMotApi\Service\MotTestStatusService;
use DvsaMotApiTest\Service\MotTestServiceTest;
use VehicleApi\Service\VehicleSearchService;

/**
 * Class MotTestMapperTest.
 */
class MotTestMapperTest extends AbstractServiceTestCase
{
    const MOCK_BRAKE_TEST_RESULT_SERVICE = 'mockBrakeTestResultService';
    const MOCK_VEHICLE_SERVICE = 'mockVehicleService';
    const MOCK_HYDRATOR = 'mockHydrator';
    const MOCK_CERTIFICATE_EXPIRY_SERVICE = 'mockCertificateExpiryService';
    const MOCK_STATUS_SERVICE = 'mockStatusService';
    const MOCK_DATE_SERVICE = 'mockDateService';
    const MOCK_PARAMOBFUSCATOR = 'paramObfuscator';
    const MOCK_DEFECT_SENTENCE_CASE_CONVERTER = 'mockDefectSentenceCaseConverterService';

    public function testMotTestMappedCorrectlyToDto()
    {
        $motTestNumber = 1;
        $vehicleClass = '4';

        $tester = $this->createTester();
        $testType = (new MotTestType())->setCode(MotTestTypeCode::NORMAL_TEST);

        $countryOfRegistration = (new CountryOfRegistration())->setName('COR');

        $make = (new Make())->setName('MAKE');
        $model = (new Model())->setName('MODEL')->setMake($make);

        $modelDetail = new ModelDetail();
        $modelDetail
            ->setModel($model)
            ->setVehicleClass(new VehicleClass($vehicleClass))
            ->setFuelType(new FuelType())
            ->setBodyType(new BodyType());

        $vehicle = new Vehicle();
        $vehicle
            ->setVersion(1)
            ->setModelDetail($modelDetail)
            ->setColour(new Colour())
            ->setNewAtFirstReg(true)
            ->setCountryOfRegistration($countryOfRegistration);

        $address = new Address();
        $address->setAddressLine1('Johns Garage');

        $site = new Site();

        $contactDetail = (new ContactDetail())->setAddress($address);
        $contactType = (new SiteContactType())->setCode(SiteContactTypeCode::BUSINESS);
        $site->setContact($contactDetail, $contactType);

        $site->setType(new SiteType());

        $this->addOrg($site);

        $brakeTestResult = new BrakeTestResultClass3AndAbove();
        $brakeTestResultClass12 = new BrakeTestResultClass12();
        $motRfrAdvisory = self::getTestMotTestReasonForRejection('ADVISORY');
        $testDate = DateUtils::toDate('2013-09-30');
        $dateHolder = new TestDateTimeHolder($testDate);

        $motTest = new MotTest();
        /** @var \DateTime $startedDate */
        $startedDate = $dateHolder->getCurrent();
        $motTest->setStartedDate(clone $startedDate);
        $expiryDate = clone $startedDate;
        $expiryDate->add(\DateInterval::createFromDateString('+1 year -1 day'));

        $motTest
            ->setStatus($this->createMotTestActiveStatusMock())
            ->setNumber($motTestNumber)
            ->setTester($tester)
            ->setMotTestType($testType)
            ->setVehicle($vehicle)
            ->setVehicleVersion($vehicle->getVersion())
            ->setVehicleTestingStation($site)
            ->setBrakeTestResultClass3AndAbove($brakeTestResult)
            ->setBrakeTestResultClass12($brakeTestResultClass12)
            ->setPrsMotTest((new MotTest())->setNumber(2))
            ->addMotTestReasonForRejection($motRfrAdvisory);

        $vtsData = [
            'id' => 3,
            'address' => 'Johns Garage',
            'authorisedExaminer' => 42,
            'comments' => [],
            'primaryTelephone' => null,
        ];

        $brakeTestData = [
            'id' => 3,
            'generalPass' => 'true',
        ];

        $vehicleDto = (new VehicleDto())
            ->setVehicleClass(
                (new VehicleClassDto())
                    ->setCode($vehicle->getVehicleClass()->getCode())
            )
            ->setColour(
                (new ColourDto())
                    ->setName($vehicle->getColour()->getName())
            )
            ->setFuelType(
                (new VehicleParamDto())
                    ->setName($vehicle->getFuelType()->getName())
            )
            ->setCountryOfRegistration(
                (new CountryDto())->setCode($countryOfRegistration->getCode())
                    ->setName($countryOfRegistration->getName())
                    ->setLicensingCode($countryOfRegistration->getLicensingCopy())

            )
            ->setMakeName($vehicle->getMakeName())
            ->setModelName($vehicle->getModelName())
            ->setBodyType(new VehicleParamDto())
            ->setTransmissionType(new VehicleParamDto())
            ->setIsNewAtFirstReg($vehicle->isNewAtFirstReg());

        $expectedData = (new MotTestDto())
            ->setStatus(MotTestStatusName::ACTIVE)
            ->setMotTestNumber($motTestNumber)
            ->setStartedDate(DateTimeApiFormat::dateTime($startedDate))
            ->setTester(
                (new PersonDto())
                    ->setId(1)
                    ->setUsername('tester1')
                    ->setContactDetails(
                        [
                            (new ContactDto())->setEmails(([new EmailDto()])),
                        ]
                    )
            )
            ->setVehicle($vehicleDto)
            ->setVehicleTestingStation($vtsData)
            ->setTestType((new MotTestTypeDto())->setCode('NT'));

        $expectedRfr1 = [
            'rfrId' => 1,
            'name' => 'Rear Stop lamp',
            'failureText' => 'adversely affected by the operation of another lamp',
            'inspectionManualReference' => '1.2.1f',
            'testItemSelectorId' => 12,
            'testItemSelectorDescription' => 'aaa',
            'markedAsRepaired' => false,
            'comment' => null,
            'type' => ReasonForRejectionTypeName::ADVISORY,
        ];

        $expectedData->setReasonsForRejection(
            [
                ReasonForRejectionTypeName::ADVISORY => [$expectedRfr1],
            ]
        );

        // Setup MotTestMapper mock
        $mocks = $this->getMocksForMotTestMapperService();

        // Setup additional expectedData that relied on motTestMapper function
        $expectedData
            ->setBrakeTestResult($brakeTestData)
            ->setPendingDetails(
                [
                    'currentSubmissionStatus' => 'INCOMPLETE',
                    'issuedDate' => null,
                    'expiryDate' => null,
                ]
            )
            ->setVehicleClass((new VehicleClassDto())->setCode('4'))
            ->setBrakeTestCount(2)
            ->setMake('MAKE')
            ->setModel('MODEL')
            ->setTesterBrakePerformanceNotTested(false)
            ->setCountryOfRegistration((new CountryDto())->setName('COR'))
            ->setPrsMotTestNumber(2)
            ->setPrimaryColour($vehicleDto->getColour())
            ->setFuelType((new FuelTypeDto()));

        $hydratorCalls = [
            [self::WITH => $site, self::WILL => $vtsData],
            [self::WITH => $motRfrAdvisory, self::WILL => $expectedRfr1],
        ];
        $this->setupHandlerForHydratorMultipleCalls($mocks['mockHydrator'], $hydratorCalls);

        $mocks['mockBrakeTestResultService']
            ->expects($this->once())
            ->method('extract')
            ->with($brakeTestResult)
            ->will($this->returnValue($brakeTestData));

        $mocks[self::MOCK_STATUS_SERVICE]
            ->expects($this->once())
            ->method('hasUnrepairedBrakePerformanceNotTestedRfr')
            ->with($motTest)
            ->will($this->returnValue(false));

        $mocks[self::MOCK_STATUS_SERVICE]
            ->expects($this->any())
            ->method('getMotTestPendingStatus')
            ->with($motTest)
            ->will($this->returnValue('INCOMPLETE'));

        $motTestMapper = $this->constructMotTestMapperWithMocks($mocks);
        $this->mockClassField($motTestMapper, 'dateTimeHolder', $dateHolder);

        $resultMotTestData = $motTestMapper->mapMotTest($motTest);
        $this->assertEquals($expectedData, $resultMotTestData);
    }

    public function testMotTestOriginalPopulated()
    {
        $motTest = MotTestServiceTest::getTestMotTestEntity();
        $motTest->setStatus($this->createMotTestActiveStatusMock());
        $motTest->setMotTestIdOriginal(clone $motTest);
        $mocks = $this->getMocksForMotTestMapperService();

        $motTestMapper = $this->constructMotTestMapperWithMocks($mocks);
        /** @var MotTestDto $resultMotTestData */
        $resultMotTestData = $motTestMapper->mapMotTest($motTest);

        $original = $resultMotTestData->getMotTestOriginal();
        $resultMotTestData->setMotTestOriginal(null);
        $this->assertEquals($resultMotTestData, $original);
    }

    /**
     * @dataProvider motTestsForClass1And2DataProvider
     *
     * @param MotTest $motTest
     */
    public function testDefaultBrakeTestTypeSetCorrectlyForClass1And2(MotTest $motTest)
    {
        $vtsJson = $this->getMappedMotTest($motTest)->getVehicleTestingStation();
        $actualBrakeTestCode = ArrayUtils::tryGet($vtsJson, 'defaultBrakeTestClass1And2');

        $expectedBrakeTestCode = null;
        $defaultBrakeTestType = $motTest->getVehicleTestingStation()->getDefaultBrakeTestClass1And2();
        if (isset($defaultBrakeTestType)) {
            $expectedBrakeTestCode = $defaultBrakeTestType->getCode();
        }

        $this->assertEquals($expectedBrakeTestCode, $actualBrakeTestCode);
    }

    /**
     * @dataProvider motTestsForClass3AndAboveDataProvider
     *
     * @param MotTest $motTest
     */
    public function testMapMotTestMinimalMappedCorrectlyBrakeTestClass3AndAbove(MotTest $motTest)
    {
        $mappedMotTestVts = $this->getMappedMotTest($motTest)->getVehicleTestingStation();
        $unmappedMotTestVts = $motTest->getVehicleTestingStation();

        if ($unmappedMotTestVts->getDefaultServiceBrakeTestClass3AndAbove()) {
            $brakeTest = $unmappedMotTestVts->getDefaultServiceBrakeTestClass3AndAbove();
            $this->assertEquals(
                $brakeTest->getCode(), $mappedMotTestVts['defaultServiceBrakeTestClass3AndAbove']
            );
        } else {
            $defaultServiceBrakeTestClass3AndAbove = null;

            if (array_key_exists('defaultServiceBrakeTestClass3AndAbove', $mappedMotTestVts)) {
                $defaultServiceBrakeTestClass3AndAbove = $mappedMotTestVts['defaultServiceBrakeTestClass3AndAbove'];
            }

            $this->assertNull($defaultServiceBrakeTestClass3AndAbove);
        }

        if ($unmappedMotTestVts->getDefaultParkingBrakeTestClass3AndAbove()) {
            $brakeTest = $unmappedMotTestVts->getDefaultParkingBrakeTestClass3AndAbove();
            $this->assertEquals(
                $brakeTest->getCode(), $mappedMotTestVts['defaultParkingBrakeTestClass3AndAbove']
            );
        } else {
            $defaultParkingBrakeTestClass3AndAbove = null;

            if (array_key_exists('defaultParkingBrakeTestClass3AndAbove', $mappedMotTestVts)) {
                $defaultParkingBrakeTestClass3AndAbove = $mappedMotTestVts['defaultParkingBrakeTestClass3AndAbove'];
            }

            $this->assertNull($defaultParkingBrakeTestClass3AndAbove);
        }
    }

    private function createTester()
    {
        $tester = new Person();
        $tester->setId(1);
        $tester->setUsername('tester1');

        return $tester;
    }

    /**
     * @param MotTest $motTest
     *
     * @return MotTestDto
     */
    private function getMappedMotTest(MotTest $motTest)
    {
        $mocks = $this->getMocksForMotTestMapperService();
        $motTestMapper = $this->constructMotTestMapperWithMocks($mocks);

        return $motTestMapper->mapMotTestMinimal($motTest);
    }

    /**
     * @param Site $vehicleTestingStation
     */
    private function addOrg(Site $vehicleTestingStation)
    {
        $org = new Organisation();
        $org->setSlotBalance(MotTestServiceTest::SLOTS_COUNT_START);
        $org->setId(9);
        $org->setAuthorisedExaminer(
            (new AuthorisationForAuthorisedExaminer())
                ->setId(42)
        );
        $vehicleTestingStation->setOrganisation($org);
    }

    private function getMocksForMotTestMapperService()
    {
        $mockHydrator = $this->getMockHydrator();

        $mockBrakeTestResultService = $this->getMockWithDisabledConstructor(
            BrakeTestResultService::class
        );
        $mockVehicleSearchService = $this->getMockWithDisabledConstructor(VehicleSearchService::class);
        $mockCertificateExpiryService = $this->getMockWithDisabledConstructor(
            CertificateExpiryService::class
        );
        $motTestStatusService = $this->getMockWithDisabledConstructor(MotTestStatusService::class);

        $motTestDateService = $this->getMockWithDisabledConstructor(MotTestDateHelperService::class);

        $mockParamObfuscator = $this->getMockWithDisabledConstructor(ParamObfuscator::class);

        $defectSentenceCaseConverter = $this->getMockWithDisabledConstructor(DefectSentenceCaseConverter::class);

        return [
            self::MOCK_BRAKE_TEST_RESULT_SERVICE => $mockBrakeTestResultService,
            self::MOCK_VEHICLE_SERVICE => $mockVehicleSearchService,
            self::MOCK_HYDRATOR => $mockHydrator,
            self::MOCK_CERTIFICATE_EXPIRY_SERVICE => $mockCertificateExpiryService,
            self::MOCK_STATUS_SERVICE => $motTestStatusService,
            self::MOCK_DATE_SERVICE => $motTestDateService,
            self::MOCK_PARAMOBFUSCATOR => $mockParamObfuscator,
            self::MOCK_DEFECT_SENTENCE_CASE_CONVERTER => $defectSentenceCaseConverter,
        ];
    }

    /**
     * @param $mocks
     *
     * @return MotTestMapper
     */
    private function constructMotTestMapperWithMocks($mocks)
    {
        return new MotTestMapper(
            $mocks[self::MOCK_HYDRATOR],
            $mocks[self::MOCK_BRAKE_TEST_RESULT_SERVICE],
            $mocks[self::MOCK_VEHICLE_SERVICE],
            $mocks[self::MOCK_CERTIFICATE_EXPIRY_SERVICE],
            $mocks[self::MOCK_STATUS_SERVICE],
            $mocks[self::MOCK_DATE_SERVICE],
            $mocks[self::MOCK_PARAMOBFUSCATOR],
            $mocks[self::MOCK_DEFECT_SENTENCE_CASE_CONVERTER]
        );
    }

    /**
     * @param string $type
     *
     * @return MotTestReasonForRejection
     */
    private function getTestMotTestReasonForRejection($type = 'FAIL')
    {
        $motTestRfr = new MotTestReasonForRejection();
        $motTestRfr->setType((new ReasonForRejectionType())->setReasonForRejectionType($type));

        $rfr = new ReasonForRejection();
        $rfr->setRfrId(1);
        $rfr->setInspectionManualReference('1.2.1f');
        $rfrDescriptions = [
            (new ReasonForRejectionDescription())
                ->setLanguage((new Language())->setCode('EN'))
                ->setName('adversely affected by the operation of another lamp'),
        ];
        $rfr->setDescriptions($rfrDescriptions);

        $rfrCategory = new TestItemSelector();
        $rfrCategory->setId(12);
        $rfrCategory->setDescriptions(
            [
                (new TestItemCategoryDescription())
                    ->setLanguage((new Language())->setCode('EN'))
                    ->setName('Rear lamp'),
            ]
        );
        $rfr->setTestItemSelector($rfrCategory);

        $motTestRfr->setReasonForRejection($rfr);

        return $motTestRfr;
    }

    private function createMotTestActiveStatusMock()
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method('getName')
            ->willReturn(MotTestStatusName::ACTIVE);

        return $status;
    }

    public function motTestsForClass1And2DataProvider()
    {
        $site1 = $this->createSite();

        $site2 = $this->createSite()
            ->setDefaultBrakeTestClass1And2(
            (new BrakeTestType())->setCode(BrakeTestTypeCode::DECELEROMETER)
        );

        $site3 = $this->createSite()
            ->setDefaultBrakeTestClass1And2(
            (new BrakeTestType())->setCode(BrakeTestTypeCode::GRADIENT)
        );

        return [
            [$this->createMotTest($site1)],
            [$this->createMotTest($site2)],
            [$this->createMotTest($site3)],
        ];
    }

    public function motTestsForClass3AndAboveDataProvider()
    {
        $decelerometerBrakeTestType = new BrakeTestType();
        $decelerometerBrakeTestType
            ->setCode(BrakeTestTypeCode::DECELEROMETER)
            ->setId(1);

        $gradientBrakeTestType = new BrakeTestType();
        $gradientBrakeTestType
            ->setCode(BrakeTestTypeCode::GRADIENT)
            ->setId(2);

        $site1 = $this->createSite();
        $site1->setDefaultServiceBrakeTestClass3AndAbove($decelerometerBrakeTestType);

        $site2 = $this->createSite();
        $site2->setDefaultParkingBrakeTestClass3AndAbove($gradientBrakeTestType);

        $site3 = $this->createSite();
        $site3
            ->setDefaultServiceBrakeTestClass3AndAbove($gradientBrakeTestType)
            ->setDefaultParkingBrakeTestClass3AndAbove($decelerometerBrakeTestType);

        return [
            [$this->createMotTest($site1)],
            [$this->createMotTest($site2)],
            [$this->createMotTest($site3)],
        ];
    }

    private function createSite()
    {
        $address = new Address();
        $address
            ->setAddressLine1('address line 1')
            ->setAddressLine2('address line 2')
            ->setAddressLine3('address line 3')
            ->setCountry('England')
            ->setPostcode('postcode')
            ->setTown('London');

        $phoneContactType = new PhoneContactType();
        $phoneContactType->setCode(PhoneContactTypeCode::BUSINESS);

        $phone = new Phone();
        $phone
            ->setContactType($phoneContactType)
            ->setNumber('658 876 678')
            ->setIsPrimary(true);

        $contactDetail = new ContactDetail();
        $contactDetail
            ->setAddress($address)
            ->addPhone($phone);

        $siteContactType = new SiteContactType();
        $siteContactType->setCode(SiteContactTypeCode::BUSINESS);

        $site = new Site();
        $site
            ->setId(1)
            ->setContact($contactDetail, $siteContactType);

        return $site;
    }

    /**
     * @param Site|null $site
     *
     * @return MotTest
     */
    private function createMotTest(Site $site = null)
    {
        $vehicleClass = new VehicleClass();
        $vehicleClass->setCode(VehicleClassCode::CLASS_3);

        $modelDetail = new ModelDetail();
        $modelDetail->setVehicleClass($vehicleClass);

        $countryOfRegistration = (new CountryOfRegistration())->setName('COR');

        $vehicle = new Vehicle();
        $vehicle->setVersion(1);
        $vehicle->setCountryOfRegistration($countryOfRegistration);
        $vehicle->setModelDetail($modelDetail);

        $motTest = new MotTest();
        $motTest
            ->setVehicleVersion($vehicle->getVersion())
            ->setVehicleTestingStation($site)
            ->setStatus(new MotTestStatus())
            ->setVehicle($vehicle)
            ->setPrsMotTest((new MotTest())->setNumber(2))
            ;

        return $motTest;
    }
}
