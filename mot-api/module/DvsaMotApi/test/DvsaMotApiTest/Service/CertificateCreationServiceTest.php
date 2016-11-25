<?php

namespace DvsaMotApiTest\Service;

use DataCatalogApi\Service\DataCatalogService;
use DateInterval;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Common\ColourDto;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Dto\Vehicle\CountryDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\MysteryShopper\MysteryShopperExpiryDateGenerator;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaDocument\Service\Document\DocumentService;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Domain\DvsaContactDetails\DvsaContactDetailsConfiguration;
use DvsaMotApi\Mapper\AbstractMotTestMapper;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApiTest\Test\ReasonForRejectionBuilder;
use NumberFormatter;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class CertificateCreationServiceTest
 *
 * @package DvsaMotApiTest\Service
 */
class CertificateCreationServiceTest extends AbstractServiceTestCase
{
    /** @var DocumentService|MockObj */
    protected $mockDocumentService;

    /** @var MotTestService|MockObj */
    protected $mockMotService;

    /** @var DataCatalogService */
    private $catalog;

    /** @var DvsaContactDetailsConfiguration $dvsaContactDetailsConfiguration */
    private $dvsaContactDetailsConfiguration;

    /** @var CertificateCreationService|MockObj */
    protected $service;

    /** @var FeatureToggles|MockObj */
    private $mockFeatureToggles;

    public function setup()
    {
        $this->mockDocumentService = $this->getMockWithDisabledConstructor(
            \DvsaDocument\Service\Document\DocumentService::class
        );

        $this->mockMotService = $this->getMockWithDisabledConstructor(
            MotTestService::class
        );

        $this->catalog = XMock::of(DataCatalogService::class);

        $this->dvsaContactDetailsConfiguration = new DvsaContactDetailsConfiguration([
            'name' => 'Driver Vehicles & Standards Agency',
            'phone' => '03001239000',
        ]);

        $this->mockFeatureToggles = $this->getMockWithDisabledConstructor(
            FeatureToggles::class
        );

        $this->service = new CertificateCreationService(
            $this->mockMotService,
            $this->mockDocumentService,
            $this->catalog,
            $this->dvsaContactDetailsConfiguration,
            $this->mockFeatureToggles
        );
    }

    /**
     * @dataProvider nonStandardMotTestTypesDataProvider
     *
     * @param $testTypeCode
     */
    public function testCreateWithValidDataAndTestTypeExpectsAdvisory($testTypeCode)
    {
        $motTestId = 1;
        $documentId = 7;

        $this->mockDocumentService->expects($this->once())
            ->method('createSnapshot')
            ->with('MOT-Advisory-Notice')
            ->will($this->returnValue($documentId));

        $motTestData = (new MotTestDto())
            ->setId($motTestId)
            ->setDocument($documentId)
            ->setExpiryDate('2015-01-01')
            ->setIssuedDate('2014-01-01')
            ->setTester(
                (new PersonDto())
                    ->setDisplayName('Testy McTest')
            )
            ->setVehicleClass((new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4))
            ->setTestType((new MotTestTypeDto())->setCode($testTypeCode))
            ->setVehicleTestingStation(
                [
                    'name'       => 'Montys Mots',
                    'siteNumber' => 'asdfasda',
                    'primaryTelephone' => '011712013243',
                ]
            )
            ->setPrimaryColour((new ColourDto())->setName('Primary'))
            ->setSecondaryColour((new ColourDto())->setName('Secondary'))
            ->setCountryOfRegistration((new CountryDto())->setName('UK'))
            ->setReasonsForRejection(ReasonForRejectionBuilder::create());

        $additionalData = [
            'TestStationAddress'    => []
        ];

        $this->mockMotService->expects($this->once())
            ->method('getAdditionalSnapshotData')
            ->will($this->returnValue($additionalData));

        $this->mockMotService->expects($this->once())
            ->method('updateDocument')
            ->with($motTestId, $documentId);

        /** @var MotTestDto $result */
        $result = $this->service->create(1, $motTestData, 1);
        $this->assertEquals($motTestId, $result->getId());
        $this->assertEquals($documentId, $result->getDocument());
    }

    public function nonStandardMotTestTypesDataProvider()
    {
        return [
            [MotTestTypeCode::TARGETED_REINSPECTION],
            [MotTestTypeCode::MOT_COMPLIANCE_SURVEY],
        ];
    }

    public function testCreateWithValidDataAndPassed()
    {
        $motTestId = 1;
        $documentId = 7;

        $this->mockDocumentService->expects($this->once())
            ->method('createSnapshot')
            ->with('MOT-Pass-Certificate')
            ->will($this->returnValue($documentId));

        $motTestData = (new MotTestDto())
            ->setId($motTestId)
            ->setDocument($documentId)
            ->setExpiryDate('2015-01-01')
            ->setIssuedDate('2014-01-01')
            ->setTester(
                (new PersonDto())
                    ->setDisplayName('Testy McTest')
                    ->setFirstName('Testy')
                    ->setFamilyName('McTest')
            )
            ->setVehicleClass((new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4))
            ->setStatus('PASSED')
            ->setVehicleTestingStation(
                [
                    'name'       => 'Montys Mots',
                    'siteNumber' => 'asdfasda',
                    'primaryTelephone' => '011712013243',
                ]
            )
            ->setPrimaryColour((new ColourDto())->setName('Primary'))
            ->setSecondaryColour((new ColourDto())->setName('Secondary'))
            ->setCountryOfRegistration((new CountryDto())->setName('UK'))
            ->setReasonsForRejection(ReasonForRejectionBuilder::create());

        $additionalData = [
            'TestStationAddress'    => []
        ];

        $this->mockMotService->expects($this->once())
            ->method('getAdditionalSnapshotData')
            ->will($this->returnValue($additionalData));

        $this->mockMotService->expects($this->once())
            ->method('updateDocument')
            ->with($motTestId, $documentId);

        /** @var MotTestDto $result */
        $result = $this->service->create(1, $motTestData, 1);
        $this->assertEquals($motTestId, $result->getId());
        $this->assertEquals($documentId, $result->getDocument());
    }

    public function testCreateWithValidDataAndFailed()
    {
        $motTestId = 1;
        $documentId = 7;

        $this->mockDocumentService->expects($this->once())
            ->method('createSnapshot')
            ->with('MOT-Fail-Certificate')
            ->will($this->returnValue($documentId));

        $motTestData = (new MotTestDto())
            ->setId($motTestId)
            ->setDocument($documentId)
            ->setExpiryDate('2015-01-01')
            ->setIssuedDate('2014-01-01')
            ->setVehicle((new VehicleDto())->setFirstUsedDate(new \DateTime('2012-01-01')))
            ->setTester(
                (new PersonDto())
                    ->setDisplayName('Testy McTest')
                    ->setFirstName('Testy')
                    ->setFamilyName('McTest')
            )
            ->setStatus('FAILED')
            ->setVehicleTestingStation(
                [
                    'name'       => 'Montys Mots',
                    'siteNumber' => 'asdfasda',
                    'primaryTelephone' => '011712013243',
                ]
            )
            ->setPrimaryColour((new ColourDto())->setName('Primary'))
            ->setSecondaryColour((new ColourDto())->setName('Secondary'))
            ->setCountryOfRegistration((new CountryDto())->setName('UK'))
            ->setVehicleClass((new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4))
            ->setReasonsForRejection([ 0=> ['markedAsRepaired' => true]]);

        $additionalData = [
            'TestStationAddress'    => []
        ];

        $this->mockMotService->expects($this->once())
            ->method('getAdditionalSnapshotData')
            ->will($this->returnValue($additionalData));

        $this->mockMotService->expects($this->once())
            ->method('updateDocument')
            ->with($motTestId, $documentId);

        $result = $this->service->create(1, $motTestData, 1);
        $this->assertEquals($motTestId, $result->getId());
        $this->assertEquals($documentId, $result->getDocument());
    }

    public function testCreateWithValidDataAndPassedPrs()
    {
        $passId = 7;
        $failId = 8;

        $this->mockDocumentService->expects($this->at(0))
            ->method('createSnapshot')
            ->with('MOT-Pass-Certificate')
            ->will($this->returnValue($passId));

        $this->mockDocumentService->expects($this->at(1))
            ->method('createSnapshot')
            ->with('MOT-Fail-Certificate')
            ->will($this->returnValue($failId));

        $motTestId = 1;
        $prsTestId = 2;

        $motTestData = (new MotTestDto())
            ->setId($motTestId)
            ->setDocument($failId)
            ->setExpiryDate('2015-01-01')
            ->setIssuedDate('2014-01-01')
            ->setVehicle(
                (new VehicleDto())
                    ->setFirstUsedDate(new \DateTime('2012-01-01'))
                    ->setVehicleClass((new VehicleClassDto())->setCode(4))
            )
            ->setTester(
                (new PersonDto())
                    ->setDisplayName('Testy McTest')
                    ->setFirstName('Testy')
                    ->setFamilyName('McTest')
            )
            ->setStatus('FAILED')
            ->setVehicleTestingStation(
                [
                    'name'       => 'Montys Mots',
                    'siteNumber' => 'asdfasda',
                    'primaryTelephone' => '011712013243',
                ]
            )
            ->setPrsMotTestNumber($prsTestId)
            ->setPrimaryColour((new ColourDto())->setName('Primary'))
            ->setSecondaryColour((new ColourDto())->setName('Secondary'))
            ->setCountryOfRegistration((new CountryDto())->setName('UK'))
            ->setVehicleClass((new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4))
            ->setReasonsForRejection(ReasonForRejectionBuilder::create());

        $expectedPrsTestData = (new MotTestDto())
            ->setId($prsTestId)
            ->setDocument($passId)
            ->setExpiryDate('2015-01-01')
            ->setIssuedDate('2014-01-01')
            ->setVehicle(
                (new VehicleDto())
                    ->setVehicleClass((new VehicleClassDto())->setCode(4))
            )
            ->setTester(
                (new PersonDto())
                    ->setDisplayName('Testy McTest')
                    ->setFirstName('Testy')
                    ->setFamilyName('McTest')
            )
            ->setStatus('PASSED')
            ->setVehicleTestingStation(
                [
                    'name'       => 'Montys Mots',
                    'siteNumber' => 'asdfasda',
                    'primaryTelephone' => '011712013243',
                ]
            )
            ->setPrimaryColour((new ColourDto())->setName('Primary'))
            ->setSecondaryColour((new ColourDto())->setName('Secondary'))
            ->setCountryOfRegistration((new CountryDto())->setName('UK'))
            ->setVehicleClass((new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4))
            ->setReasonsForRejection(ReasonForRejectionBuilder::create());

        $this->mockMotService->expects($this->once())
            ->method('getMotTestData')
            ->with($prsTestId)
            ->will($this->returnValue($expectedPrsTestData));

        $additionalData = array(
            'TestStationAddress' => array(
            )
        );

        $this->mockMotService->expects($this->exactly(2))
            ->method('getAdditionalSnapshotData')
            ->will($this->returnValue($additionalData));

        // we expect the *first* document to be the PRS pass
        $this->mockMotService->expects($this->at(2))
            ->method('updateDocument')
            ->with($prsTestId, $passId);

        $this->mockMotService->expects($this->at(4))
            ->method('updateDocument')
            ->with($motTestId, $failId);

        $result = $this->service->create(1, $motTestData, 1);
        $this->assertEquals($motTestId, $result->getId());
        $this->assertEquals($failId, $result->getDocument());
    }

    public function testCreateWithValidDataAndFailedPrs()
    {
        $failId = 8;
        $passId = 7;

        $this->mockDocumentService->expects($this->at(0))
            ->method('createSnapshot')
            ->with('MOT-Fail-Certificate')
            ->will($this->returnValue($failId));

        $this->mockDocumentService->expects($this->at(1))
            ->method('createSnapshot')
            ->with('MOT-Pass-Certificate')
            ->will($this->returnValue($passId));

        $motTestId = 1;
        $prsTestId = 2;

        $motTestData = (new MotTestDto())
            ->setId($motTestId)
            ->setDocument($passId)
            ->setExpiryDate('2015-01-01')
            ->setIssuedDate('2014-01-01')
            ->setVehicle(
                (new VehicleDto())
                    ->setFirstUsedDate(new \DateTime('2012-01-01'))
                    ->setVehicleClass((new VehicleClassDto())->setCode(4))
            )
            ->setTester(
                (new PersonDto())
                    ->setDisplayName('Testy McTest')
                    ->setFirstName('Testy')
                    ->setFamilyName('McTest')
            )
            ->setStatus('PASSED')
            ->setVehicleTestingStation(
                [
                    'name'       => 'Montys Mots',
                    'siteNumber' => 'asdfasda',
                    'primaryTelephone' => '011712013243',
                ]
            )
            ->setPrsMotTestNumber($prsTestId)
            ->setPrimaryColour((new ColourDto())->setName('Primary'))
            ->setSecondaryColour((new ColourDto())->setName('Secondary'))
            ->setCountryOfRegistration((new CountryDto())->setName('UK'))
            ->setVehicleClass((new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4))
            ->setReasonsForRejection(ReasonForRejectionBuilder::create());

        $expectedPrsTestData = (new MotTestDto())
            ->setId($prsTestId)
            ->setDocument($failId)
            ->setExpiryDate('2015-01-01')
            ->setIssuedDate('2014-01-01')
            ->setVehicle(
                (new VehicleDto())
                    ->setVehicleClass((new VehicleClassDto())->setCode(4))
            )
            ->setTester(
                (new PersonDto())
                    ->setDisplayName('Testy McTest')
                    ->setFirstName('Testy')
                    ->setFamilyName('McTest')
            )
            ->setStatus('FAILED')
            ->setVehicleTestingStation(
                [
                    'name'       => 'Montys Mots',
                    'siteNumber' => 'asdfasda',
                    'primaryTelephone' => '011712013243',
                ]
            )
            ->setPrimaryColour((new ColourDto())->setName('Primary'))
            ->setSecondaryColour((new ColourDto())->setName('Secondary'))
            ->setCountryOfRegistration((new CountryDto())->setName('UK'))
            ->setVehicleClass((new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4))
            ->setReasonsForRejection(ReasonForRejectionBuilder::create());

        $this->mockMotService->expects($this->once())
            ->method('getMotTestData')
            ->with($prsTestId)
            ->will($this->returnValue($expectedPrsTestData));

        $additionalData = array(
            'TestStationAddress' => array(
            )
        );

        $this->mockMotService->expects($this->exactly(2))
            ->method('getAdditionalSnapshotData')
            ->will($this->returnValue($additionalData));

        $this->mockMotService->expects($this->at(2))
            ->method('updateDocument')
            ->with($prsTestId, $failId);

        $this->mockMotService->expects($this->at(4))
            ->method('updateDocument')
            ->with($motTestId, $passId);

        $result = $this->service->create(1, $motTestData, 1);
        $this->assertEquals($motTestId, $result->getId());
        $this->assertEquals($passId, $result->getDocument());
    }

    public function testCreateWithValidDataAndAbandoned()
    {
        $motTestId = 1;
        $documentId = 7;

        $this->mockDocumentService->expects($this->once())
            ->method('createSnapshot')
            ->with('MOT-Fail-Certificate')
            ->will($this->returnValue($documentId));

        $motTestData = (new MotTestDto())
            ->setId($motTestId)
            ->setDocument($documentId)
            ->setExpiryDate('2015-01-01')
            ->setIssuedDate('2014-01-01')
            ->setVehicle(
                (new VehicleDto())
                    ->setFirstUsedDate(new \DateTime('2012-01-01'))
                    ->setVehicleClass((new VehicleClassDto())->setCode(4))
            )
            ->setTester(
                (new PersonDto())
                    ->setDisplayName('Testy McTest')
                    ->setFirstName('Testy')
                    ->setFamilyName('McTest')
            )
            ->setStatus('ABANDONED')
            ->setPrimaryColour((new ColourDto())->setName('Primary'))
            ->setSecondaryColour((new ColourDto())->setName('Secondary'))
            ->setCountryOfRegistration((new CountryDto())->setName('UK'))
            ->setVehicleClass((new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4))
            ->setReasonsForRejection(ReasonForRejectionBuilder::create());

        $additionalData = [
            'vehicleTestingStation' => [
                'name'       => 'Montys Mots',
                'siteNumber' => 'asdfasda',
                'primaryTelephone' => '011712013243',
            ],
            'TestStationAddress'    => []
        ];

        $this->mockMotService->expects($this->once())
            ->method('getAdditionalSnapshotData')
            ->will($this->returnValue($additionalData));

        $this->mockMotService->expects($this->once())
            ->method('updateDocument')
            ->with($motTestId, $documentId);

        $result = $this->service->create(1, $motTestData, 1);
        $this->assertEquals($motTestId, $result->getId());
        $this->assertEquals($documentId, $result->getDocument());
    }

    public function testCreateWithValidDataAndAborted()
    {
        $motTestId = 1;
        $documentId = 7;

        $this->mockDocumentService->expects($this->once())
            ->method('createSnapshot')
            ->with('MOT-Fail-Certificate')
            ->will($this->returnValue($documentId));

        $motTestData = (new MotTestDto())
            ->setId($motTestId)
            ->setDocument($documentId)
            ->setExpiryDate('2015-01-01')
            ->setIssuedDate('2014-01-01')
            ->setVehicle(
                (new VehicleDto())
                    ->setFirstUsedDate(new \DateTime('2012-01-01'))
                    ->setVehicleClass((new VehicleClassDto())->setCode(4))
            )
            ->setTester(
                (new PersonDto())
                    ->setDisplayName('Testy McTest')
                    ->setFirstName('Testy')
                    ->setFamilyName('McTest')
            )
            ->setStatus('ABORTED')
            ->setPrimaryColour((new ColourDto())->setName('Primary'))
            ->setSecondaryColour((new ColourDto())->setName('Secondary'))
            ->setCountryOfRegistration((new CountryDto())->setName('UK'))
            ->setVehicleClass((new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4))
            ->setReasonsForRejection(ReasonForRejectionBuilder::create());

        $additionalData = [
            'vehicleTestingStation' => [
                'name'       => 'Montys Mots',
                'siteNumber' => 'asdfasda',
                'primaryTelephone' => '011712013243',
            ],
            'TestStationAddress'    => []
        ];

        $this->mockMotService->expects($this->once())
            ->method('getAdditionalSnapshotData')
            ->will($this->returnValue($additionalData));

        $this->mockMotService->expects($this->once())
            ->method('updateDocument')
            ->with($motTestId, $documentId);

        $result = $this->service->create(1, $motTestData, 1);
        $this->assertEquals($motTestId, $result->getId());
        $this->assertEquals($documentId, $result->getDocument());
    }

    public function testWithNoRecognisedCertificateOutcomeForStatus()
    {
        $this->mockMotService->expects($this->never())
            ->method('getAdditionalSnapshotData')
            ->will($this->returnValue([
                'tester' => [],
                'expiryDate' => (new \DateTime())->format('Y-m-d'),
            ]));

        $motTestDto = (new MotTestDto())->setStatus('bar');
        $this->assertEquals($motTestDto, $this->service->create(1, (new MotTestDto())->setStatus('bar'), 1));
    }

    public function testCreateWithValidDataAndPassedDualLanguageVts()
    {
        $motTestId = 1;
        $documentId = 7;

        $this->mockDocumentService->expects($this->once())
            ->method('createSnapshot')
            ->with('MOT-Pass-Certificate-Dual')
            ->will($this->returnValue($documentId));

        $motTestData = (new MotTestDto())
            ->setId($motTestId)
            ->setDocument($documentId)
            ->setExpiryDate('2015-01-01')
            ->setIssuedDate('2014-01-01')
            ->setVehicle(
                (new VehicleDto())
                    ->setVehicleClass((new VehicleClassDto())->setCode(4))
            )
            ->setTester(
                (new PersonDto())
                    ->setDisplayName('Testy McTest')
                    ->setFirstName('Testy')
                    ->setFamilyName('McTest')
            )
            ->setVehicleTestingStation(
                [
                    'name'       => 'Montys Mots',
                    'siteNumber' => 'asdfasda',
                    'dualLanguage' => true,
                    'primaryTelephone' => '011712013243',
                ]
            )
            ->setStatus('PASSED')
            ->setPrimaryColour((new ColourDto())->setName('Primary'))
            ->setSecondaryColour((new ColourDto())->setName('Secondary'))
            ->setCountryOfRegistration((new CountryDto())->setName('UK'))
            ->setVehicleClass((new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4))
            ->setReasonsForRejection(ReasonForRejectionBuilder::create());

        $additionalData = [
            'TestStationAddress'    => []
        ];

        $this->mockMotService->expects($this->once())
            ->method('getAdditionalSnapshotData')
            ->will($this->returnValue($additionalData));

        $this->service->create(1, $motTestData, 1);
    }

    public function testCreateWithValidDataAndTestIsNonMotInspection()
    {
        $motTestId = 1;
        $documentId = 7;

        $this->mockDocumentService
            ->expects($this->once())
            ->method('createSnapshot')
            ->with('MOT-Advisory-Notice')
            ->will($this->returnValue($documentId));

        $motTestData = (new MotTestDto())
            ->setId($motTestId)
            ->setDocument($documentId)
            ->setExpiryDate('2015-01-01')
            ->setIssuedDate('2014-01-01')
            ->setVehicle((new VehicleDto())->setFirstUsedDate(new \DateTime('2012-01-01')))
            ->setTester(
                (new PersonDto())
                    ->setDisplayName('Testy McTest')
            )
            ->setVehicleClass((new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4))
            ->setStatus('PASSED')
            ->setTestType((new MotTestTypeDto())->setCode(MotTestTypeCode::NON_MOT_TEST))
            ->setVehicleTestingStation(
                [
                    'name'       => 'Montys Mots',
                    'siteNumber' => 'asdfasda',
                    'primaryTelephone' => '011712013243',
                ]
            )
            ->setPrimaryColour((new ColourDto())->setName('Primary'))
            ->setSecondaryColour((new ColourDto())->setName('Secondary'))
            ->setCountryOfRegistration((new CountryDto())->setName('UK'))
            ->setReasonsForRejection(ReasonForRejectionBuilder::create());

        $additionalData = [
            'TestStationAddress'    => []
        ];

        $this->mockMotService->expects($this->once())
            ->method('getAdditionalSnapshotData')
            ->will($this->returnValue($additionalData));

        $this->mockMotService->expects($this->once())
            ->method('updateDocument')
            ->with($motTestId, $documentId);

        $certificateDataBeforeAmendedDuringNonMotInspection = [
            'TestNumber' => 'test',
            'TestStation' => 'test',
            'InspectionAuthority' => 'test',
            'Odometer' => 'test',
            'IssuedDate' => 'test',
            'IssuersName' => 'test',
            'VRM' => 'test',
            'VIN' => 'test',
            'Make' => 'test',
            'Model' => 'test',
            'CountryOfRegistration' => 'test',
            'TestClass' => 'test',
            'Colour' => 'test',
            'AdvisoryInformation' => 'test',
            'ExpiryDate' => 'test',
            'AdditionalInformation' => 'test',
        ];

        $certificateDataAfterAmendedDuringNonMotInspection = [
            'TestNumber' => '',
            'TestStation' => '',
            'InspectionAuthority' => 'Driver Vehicles & Standards Agency' . "\n" . 'Telephone number - 03001239000',
            'Odometer' => 'Not recorded',
            'IssuedDate' => '1 Jan 2014',
            'IssuersName' => '',
            'VRM' => '',
            'VIN' => '',
            'Make' => '',
            'Model' => '',
            'CountryOfRegistration' => 'UK',
            'TestClass' => '4',
            'Colour' => 'Primary and Secondary',
            'AdvisoryInformation' => '',
        ];

        $motTestMapperMock = $this
            ->getMockBuilder(AbstractMotTestMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $motTestMapperMock
            ->expects($this->any())
            ->method('mapData')
            ->willReturn($certificateDataBeforeAmendedDuringNonMotInspection);

        $this->mockDocumentService
            ->expects($this->once())
            ->method('createSnapshot')
            ->with('MOT-Advisory-Notice', $certificateDataAfterAmendedDuringNonMotInspection, 1);

        /** @var MotTestDto $result */
        $result = $this->service->create(1, $motTestData, 1);
        $this->assertEquals($motTestId, $result->getId());
        $this->assertEquals($documentId, $result->getDocument());
    }

    public function testCreateWithValidDataAndTestIsMysteryShopper()
    {
        $motTestId = 1;
        $documentId = 7;

        $this->mockFeatureToggles
            ->expects($this->any())
            ->method('isEnabled')
            ->with(FeatureToggle::MYSTERY_SHOPPER)
            ->willReturn(true);

        $this->mockDocumentService
            ->expects($this->once())
            ->method('createSnapshot')
            ->with('MOT-Pass-Certificate')
            ->will($this->returnValue($documentId));

        $motTestData = (new MotTestDto())
            ->setId($motTestId)
            ->setDocument($documentId)
            ->setExpiryDate('2015-01-01')
            ->setIssuedDate('2014-01-01')
            ->setVehicle((new VehicleDto())->setFirstUsedDate(new \DateTime('2012-01-01')))
            ->setTester(
                (new PersonDto())
                    ->setDisplayName('Testy McTest')
            )
            ->setVehicleClass((new VehicleClassDto())->setCode(VehicleClassCode::CLASS_4))
            ->setStatus('PASSED')
            ->setTestType((new MotTestTypeDto())->setCode(MotTestTypeCode::MYSTERY_SHOPPER))
            ->setVehicleTestingStation(
                [
                    'name'       => 'Montys Mots',
                    'siteNumber' => 'asdfasda',
                    'primaryTelephone' => '011712013243',
                ]
            )
            ->setPrimaryColour((new ColourDto())->setName('Primary'))
            ->setSecondaryColour((new ColourDto())->setName('Secondary'))
            ->setCountryOfRegistration((new CountryDto())->setName('UK'))
            ->setReasonsForRejection(ReasonForRejectionBuilder::create());

        $additionalData = [
            'TestStationAddress'    => []
        ];

        $this->mockMotService->expects($this->once())
            ->method('getAdditionalSnapshotData')
            ->will($this->returnValue($additionalData));

        $this->mockMotService->expects($this->once())
            ->method('updateDocument')
            ->with($motTestId, $documentId);

        $certificateDataBeforeAmendedDuringMysteryShopper = [
            'TestNumber' => 'test',
            'TestStation' => 'test',
            'InspectionAuthority' => 'test',
            'Odometer' => 'test',
            'IssuedDate' => 'test',
            'IssuersName' => 'test',
            'VRM' => 'test',
            'VIN' => 'test',
            'Make' => 'test',
            'Model' => 'test',
            'CountryOfRegistration' => 'test',
            'TestClass' => 'test',
            'Colour' => 'test',
            'AdvisoryInformation' => 'test',
            'ExpiryDate' => 'test',
            'AdditionalInformation' => 'test',
        ];

        $expiryDate = (new MysteryShopperExpiryDateGenerator())->getCertificateExpiryDate();
        $expiryDateFormatted = date_format($expiryDate,"j F Y");
        $numberFormatter = new NumberFormatter("en", NumberFormatter::SPELLOUT);
        $year = date_format($expiryDate,"y");
        $yearFormatted = $numberFormatter->format((int) $year);

        $expiryDateOnCert = $expiryDateFormatted . ' (' . strtoupper($yearFormatted) . ')';
        $renewalDateOnCert = date_format($expiryDate->sub(new DateInterval('P1M'))->add(new DateInterval('P1D')),"j F Y");

        $certificateDataAfterAmendedDuringMysteryShopper = [
            'TestNumber' => '',
            'TestStation' => 'asdfasda',
            'InspectionAuthority' => 'Montys Mots' . "\n" . '		011712013243' . "\n",
            'Odometer' => 'Not recorded',
            'IssuedDate' => '1 Jan 2014',
            'IssuersName' => '',
            'VRM' => '',
            'VIN' => '',
            'Make' => '',
            'Model' => '',
            'CountryOfRegistration' => 'UK',
            'TestClass' => '4',
            'Colour' => 'Primary and Secondary',
            'AdvisoryInformation' => '',
            'ExpiryDate' => $expiryDateOnCert,
            'AdditionalInformation' => 'To preserve the anniversary of the expiry date, the earliest you can present your vehicle for test is ' . $renewalDateOnCert . '.'
        ];

        $motTestMapperMock = $this
            ->getMockBuilder(AbstractMotTestMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $motTestMapperMock
            ->expects($this->any())
            ->method('mapData')
            ->willReturn($certificateDataBeforeAmendedDuringMysteryShopper);

        $this->mockDocumentService
            ->expects($this->once())
            ->method('createSnapshot')
            ->with('MOT-Pass-Certificate', $certificateDataAfterAmendedDuringMysteryShopper, 1);

        /** @var MotTestDto $result */
        $result = $this->service->create(1, $motTestData, 1);
        $this->assertEquals($motTestId, $result->getId());
        $this->assertEquals($documentId, $result->getDocument());
    }
}
