<?php
namespace PersonApiTest\Service;

use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaEntities\Entity\QualificationAward;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\VehicleClassGroup;
use DvsaEntities\Repository\QualificationAwardRepository;
use DvsaEntities\Repository\VehicleClassGroupRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use PersonApi\Dto\PersonDetails;
use PersonApi\Service\MotTestingCertificate\Event\MotTestingCertificateEvent;
use PersonApi\Service\Mapper\MotTestingCertificateMapper;
use PersonApi\Service\MotTestingCertificateService;
use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use PersonApi\Assertion\ReadMotTestingCertificateAssertion;
use DvsaCommon\Auth\Assertion\CreateMotTestingCertificateAssertion;
use DvsaCommon\Auth\Assertion\UpdateMotTestingCertificateAssertion;
use PersonApi\Service\Validator\MotTestingCertificateValidator;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\DtoSerialization\DtoReflector;
use DvsaCommon\DtoSerialization\DtoConvertibleTypesRegistry;
use PersonApi\Service\Mapper\TesterGroupAuthorisationMapper;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;
use PersonApi\Service\PersonalDetailsService;
use DvsaEntities\Repository\AuthorisationForTestingMotRepository;
use DvsaEntities\Repository\AuthorisationForTestingMotStatusRepository;
use DvsaEntities\Repository\VehicleClassRepository;
use PersonApi\Service\MotTestingCertificate\RemoveMotTestingCertificateService;
use PersonApi\Service\MotTestingCertificate\MotTestingCertificateNotification;

class MotTestingCertificateServiceTest extends AbstractServiceTestCase
{
    const PERSON_ID = 1234;

    /** @var QualificationAwardRepository */
    private $qualificationAwardRepository;

    /** @var VehicleClassGroupRepository */
    private $vehicleClassGroupRepository;

    /** @var SiteRepository */
    private $siteRepository;

    /** @var PersonRepository */
    private $personRepository;

    /** @var MotAuthorisationServiceInterface */
    private $authorisation;

    /** @var MotIdentityProviderInterface */
    private $motIdentity;

    /** @var MotTestingCertificateEvent */
    private $event;

    /** @var DtoReflectiveDeserializer */
    private $deserializer;

    /** @var PersonalAuthorisationForMotTestingService */
    private $personalAuthorisationForMotTestingService;

    /** @var PersonalDetailsService */
    private $personalDetailsService;

    /** @var CreateMotTestingCertificateAssertion */
    private $createMotTestingCertificateAssertion;

    /** @var UpdateMotTestingCertificateAssertion */
    private $updateMotTestingCertificateAssertion;

    /** @var AuthorisationForTestingMotRepository */
    private $authorisationForTestingMotRepository;

    /** @var MotTestingCertificateNotification */
    private $notification;

    public function setUp()
    {
        $this->qualificationAwardRepository = XMock::of(QualificationAwardRepository::class);
        $this->vehicleClassGroupRepository = XMock::of(VehicleClassGroupRepository::class);
        $this->siteRepository = XMock::of(SiteRepository::class);
        $this->event = XMock::of(MotTestingCertificateEvent::class);
        $this->authorisation = XMock::of(MotAuthorisationServiceInterface::class);

        $this->personRepository = XMock::of(PersonRepository::class);
        $this
            ->personRepository
            ->expects($this->any())
            ->method("getByIdOrUsername")
            ->willReturn((new Person())->setId(1)->setUsername("username1"));

        $this->authorisation = XMock::of(MotAuthorisationServiceInterface::class);

        $identity = XMock::of(MotIdentityInterface::class);
        $identity
            ->expects($this->any())
            ->method("getUsername")
            ->willReturn("username1");

        $this->motIdentity = XMock::of(MotIdentityProviderInterface::class);
        $this
            ->motIdentity
            ->expects($this->any())
            ->method("getIdentity")
            ->willReturn($identity);

        $this->personalAuthorisationForMotTestingService = XMock::of(PersonalAuthorisationForMotTestingService::class);

        $personalDetails = XMock::of(PersonDetails::class);
        $personalDetails
            ->expects($this->any())
            ->method("getRoles")
            ->willReturn(["system" => []]);

        $this->personalDetailsService = XMock::of(PersonalDetailsService::class);
        $this
            ->personalDetailsService
            ->expects($this->any())
            ->method("get")
            ->willReturn($personalDetails);

        $this->createMotTestingCertificateAssertion = XMock::of(CreateMotTestingCertificateAssertion::class);
        $this->updateMotTestingCertificateAssertion = XMock::of(UpdateMotTestingCertificateAssertion::class);

        $this->authorisationForTestingMotRepository = XMock::of(AuthorisationForTestingMotRepository::class);

        $this->notification = XMock::of(MotTestingCertificateNotification::class);
    }

    /**
     * @dataProvider getMotTestingCertificates
     * @param array $data
     */
    public function testGetListReturnsCertificates(array $data)
    {
        $this
            ->authorisation
            ->expects($this->any())
            ->method("assertGranted")
            ->willReturn(true);

        $this
            ->qualificationAwardRepository
            ->expects($this->any())
            ->method("findAllByPersonId")
            ->willReturn($data);

        $this
            ->personRepository
            ->expects($this->any())
            ->method("get")
            ->willReturn((new Person())->setId(self::PERSON_ID));

        $motTestingCertificateService = $this->createMotTestingCertificateService();
        $certs = $motTestingCertificateService->getList(self::PERSON_ID);

        $this->assertCount(count($data), $certs);
        if (count($data) > 0) {
            foreach ($certs as $cert) {
                $this->assertInstanceOf(MotTestingCertificateDto::class, $cert);
            }
        }
    }

    /**
     * @dataProvider getMotTestingCertificate
     * @param QualificationAward $cert
     */
    public function testGetReturnsCertificate(QualificationAward $cert = null)
    {
        $this
            ->authorisation
            ->expects($this->any())
            ->method("assertGranted")
            ->willReturn(true);

        $this
            ->qualificationAwardRepository
            ->expects($this->any())
            ->method("getOneByGroupAndPersonId")
            ->willReturn($cert);

        $this
            ->personRepository
            ->expects($this->any())
            ->method("get")
            ->willReturn((new Person())->setId(self::PERSON_ID));

        $motTestingCertificateService = $this->createMotTestingCertificateService();
        $certDto = $motTestingCertificateService->get(VehicleClassGroupCode::BIKES, self::PERSON_ID);

        $this->assertInstanceOf(MotTestingCertificateDto::class, $certDto);
    }

    /**
     * @dataProvider getMotTestingCertificatesData
     */
    public function testCreateReturnsDtoOfNewMotTestingCertificate(MotTestingCertificateDto $data)
    {
        $this
            ->authorisation
            ->expects($this->any())
            ->method("assertGranted")
            ->willReturn(true);

        $this
            ->vehicleClassGroupRepository
            ->expects($this->any())
            ->method("getByCode")
            ->willReturnCallback(function ($group) {
                $vehicleClassGroup = new VehicleClassGroup();
                $vehicleClassGroup
                    ->setId(1)
                    ->setCode($group);

                return $vehicleClassGroup;
            });

        if (empty($data->getSiteNumber())) {
            $this
                ->siteRepository
                ->expects($this->exactly(0))
                ->method("getBySiteNumber");
        } else {
            $this
                ->siteRepository
                ->expects($this->any())
                ->method("getBySiteNumber")
                ->willReturn((new Site())->setSiteNumber($data->getSiteNumber()));
        }

        $this
            ->personRepository
            ->expects($this->any())
            ->method("get")
            ->willReturn((new Person())->setId(1));

        $this
            ->authorisationForTestingMotRepository
            ->expects($this->once())
            ->method("flush");

        $this
            ->event
            ->expects($this->once())
            ->method("sendCreateEvent");

        $this
            ->notification
            ->expects($this->once())
            ->method("sendCreateNotification");

        $dto = $this->createMotTestingCertificateService()->create($data->getVehicleClassGroupCode(), $data);

        $this->assertEquals($data->getVehicleClassGroupCode(), $dto->getVehicleClassGroupCode());
        $this->assertEquals($data->getSiteNumber(), $dto->getSiteNumber());
        $this->assertEquals($data->getCertificateNumber(), $dto->getCertificateNumber());
        $this->assertEquals($data->getDateOfQualification(), $dto->getDateOfQualification());
    }

    /**
     * @dataProvider getMotTestingCertificatesData
     */
    public function testUpdateReturnsDtoOfUpdatedMotTestingCertificate(MotTestingCertificateDto $data)
    {
        $this
            ->authorisation
            ->expects($this->any())
            ->method("assertGranted")
            ->willReturn(true);

        $this
            ->vehicleClassGroupRepository
            ->expects($this->any())
            ->method("getByCode")
            ->willReturnCallback(function ($group) {
                $vehicleClassGroup = new VehicleClassGroup();
                $vehicleClassGroup
                    ->setId(1)
                    ->setCode($group);

                return $vehicleClassGroup;
            });

        $this
            ->qualificationAwardRepository
            ->expects($this->any())
            ->method("getOneByGroupAndPersonId")
            ->willReturnCallback(function () use ($data) {
                $vehicleClassGroup = new VehicleClassGroup();
                $vehicleClassGroup->setCode($data->getVehicleClassGroupCode());

                $site = null;
                if (empty($data->getSiteNumber())) {
                    $site = new Site();
                    $site
                        ->setId(1)
                        ->setSiteNumber($data->getSiteNumber());
                }

                $motTestingCertificate = new QualificationAward();
                $motTestingCertificate
                    ->setId(1)
                    ->setPerson((new Person())->setUsername($this->motIdentity->getIdentity()->getUsername()))
                    ->setVehicleClassGroup($vehicleClassGroup)
                    ->setSite($site)
                    ->setCertificateNumber("someCert")
                    ->setDateOfQualification(new \DateTime("2011-11-22"));

                return $motTestingCertificate;
            });

        if (!$data->getSiteNumber()) {
            $this
                ->siteRepository
                ->expects($this->exactly(0))
                ->method("getBySiteNumber");
        } else {
            $this
                ->siteRepository
                ->expects($this->any())
                ->method("getBySiteNumber")
                ->willReturn((new Site())->setSiteNumber($data->getSiteNumber()));
        }

        $this
            ->event
            ->expects($this->once())
            ->method("sendUpdateEvent");

        $dto = $this->createMotTestingCertificateService()->update($data->getVehicleClassGroupCode(), 1, $data);

        $this->assertEquals($data->getVehicleClassGroupCode(), $dto->getVehicleClassGroupCode());
        $this->assertEquals($data->getSiteNumber(), $dto->getSiteNumber());
        $this->assertEquals($data->getCertificateNumber(), $dto->getCertificateNumber());
        $this->assertEquals($data->getDateOfQualification(), $dto->getDateOfQualification());
    }

    public function getMotTestingCertificatesData()
    {
        return [
            [
                $this->createMotTestingCertificateDto(
                    [
                        "id" => null,
                        "vehicleClassGroupCode" => VehicleClassGroupCode::BIKES,
                        "siteNumber" => "",
                        "certificateNumber" =>"certNum123",
                        "dateOfQualification" => "2015-09-12"
                    ]
                )

            ],

            [
                $this->createMotTestingCertificateDto([
                    "id" => null,
                    "vehicleClassGroupCode" => VehicleClassGroupCode::BIKES,
                    "siteNumber" => "V1234",
                    "certificateNumber" =>"certNum123",
                    "dateOfQualification" => "2015-09-12"
                ])

            ]
        ];
    }

    private function createMotTestingCertificateDto(array $data)
    {
        return $this->createDeserializer()->deserialize($data, MotTestingCertificateDto::class);
    }

    public function getMotTestingCertificates()
    {
        return [
            [$this->createMotTestingCertificates()],
            [$this->createMotTestingCertificates(23)],
            [[$this->createMotTestingCertificate(1, VehicleClassGroupCode::BIKES)]],
            [[$this->createMotTestingCertificate(1, VehicleClassGroupCode::CARS_ETC)]],
            [[]]
        ];
    }

    public function getMotTestingCertificate()
    {
        return [
            [$this->createMotTestingCertificate(1, VehicleClassGroupCode::BIKES)],
            [$this->createMotTestingCertificate(1, VehicleClassGroupCode::CARS_ETC)],
        ];
    }

    private function createMotTestingCertificateService()
    {
        $testerGroupAuthorisationMapper = XMock::of(TesterGroupAuthorisationMapper::class);
        $testerGroupAuthorisationMapper
            ->expects($this->any())
            ->method("getAuthorisation")
            ->willReturn(new TesterAuthorisation());

        return new MotTestingCertificateService(
            new ReadMotTestingCertificateAssertion($this->authorisation, $this->motIdentity, $this->personalDetailsService),
            $this->createMotTestingCertificateAssertion,
            $this->updateMotTestingCertificateAssertion,
            new MotTestingCertificateValidator($this->siteRepository),
            $this->motIdentity,
            $this->qualificationAwardRepository,
            $this->vehicleClassGroupRepository,
            $this->siteRepository,
            $this->personRepository,
            $this->event,
            $testerGroupAuthorisationMapper,
            new MotTestingCertificateMapper(),
            $this->personalDetailsService,
            new DateTimeHolder(),
            $this->authorisationForTestingMotRepository,
            XMock::of(AuthorisationForTestingMotStatusRepository::class),
            XMock::of(VehicleClassRepository::class),
            XMock::of(RemoveMotTestingCertificateService::class),
            $this->notification
        );
    }

    /**
     * @param int $siteId
     * @return array
     */
    private function createMotTestingCertificates($siteId = null)
    {
        $certs = [];
        $certs[] = $this->createMotTestingCertificate(1, VehicleClassGroupCode::BIKES, $siteId);
        $certs[] = $this->createMotTestingCertificate(2, VehicleClassGroupCode::CARS_ETC, $siteId);

        return $certs;
    }

    /**
     * @param int $id
     * @param string $vehicleClassGroupCode
     * @param int $siteId
     * @return QualificationAward
     */
    private function createMotTestingCertificate($id, $vehicleClassGroupCode, $siteId = null)
    {
        $cert = new QualificationAward();
        $cert
            ->setId($id)
            ->setPerson((new Person())->setId(self::PERSON_ID))
            ->setVehicleClassGroup((new VehicleClassGroup())->setCode($vehicleClassGroupCode))
            ->setCertificateNumber("NUMBER" . $id)
            ->setDateOfQualification((new \DateTime()));

        if ($siteId !== null) {
            $cert->setSite((new Site())->setId($siteId)->setSiteNumber("number" . $siteId));
        }

        return $cert;
    }

    private function createDeserializer()
    {
        if ($this->deserializer === null) {
            $this->deserializer = new DtoReflectiveDeserializer();
        }

        return $this->deserializer;
    }
}
