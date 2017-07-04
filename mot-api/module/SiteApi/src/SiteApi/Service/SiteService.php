<?php

namespace SiteApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\Assertion\UpdateVtsAssertion;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\Time;
use DvsaCommon\Dto\Site\FacilityDto;
use DvsaCommon\Dto\Site\SiteContactDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\AuthorisationForTestingMotAtSiteStatusCode;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\CountryCode;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Enum\SiteStatusCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Utility\Hydrator;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\AuthorisationForTestingMotAtSite;
use DvsaEntities\Entity\AuthorisationForTestingMotAtSiteStatus;
use DvsaEntities\Entity\BrakeTestType;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\EventSiteMap;
use DvsaEntities\Entity\FacilityType;
use DvsaEntities\Entity\NonWorkingDayCountry;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Entity\SiteFacility;
use DvsaEntities\Entity\SiteType;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Repository\AuthorisationForTestingMotAtSiteStatusRepository;
use DvsaEntities\Repository\BrakeTestTypeRepository;
use DvsaEntities\Repository\FacilityTypeRepository;
use DvsaEntities\Repository\NonWorkingDayCountryRepository;
use DvsaEntities\Repository\SiteContactTypeRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\SiteRiskAssessmentRepository;
use DvsaEntities\Repository\SiteStatusRepository;
use DvsaEntities\Repository\SiteTestingDailyScheduleRepository;
use DvsaEntities\Repository\SiteTypeRepository;
use DvsaEntities\Repository\VehicleClassRepository;
use DvsaEventApi\Service\EventService;
use SiteApi\Service\Mapper\SiteBusinessRoleMapMapper;
use SiteApi\Service\Mapper\VtsMapper;
use SiteApi\Service\Validator\SiteValidator;

/**
 * Service which creates/edits new VTS.
 */
class SiteService extends AbstractService
{
    const ENGLAND_COUNTRY_CODE = 'GBENG';
    const MONDAY = 1;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;

    use ExtractSiteTrait;

    const SITE_NUMBER_QUERY_PARAMETER = 'siteNumber';
    const SITE_NUMBER_REQUIRED_DISPLAY_MESSAGE = 'You need to enter a Site Number to perform the search';
    const SITE_NUMBER_INVALID_DATA_DISPLAY_MESSAGE = 'Site number should contain alphanumeric characters only';

    /** @var AuthorisationServiceInterface $authService */
    protected $authService;
    /** @var MotIdentityInterface */
    private $identity;
    /** @var ContactDetailsService */
    private $contactService;
    /** @var EventService $eventService */
    private $eventService;
    /** @var SiteRepository */
    private $repository;
    /** @var SiteTypeRepository */
    private $siteTypeRepository;
    /** @var SiteRiskAssessmentRepository */
    private $siteRiskAssessmentRepository;
    /** @var BrakeTestTypeRepository */
    private $brakeTestTypeRepository;
    /** @var SiteContactTypeRepository */
    private $siteContactTypeRepository;
    /** @var FacilityTypeRepository */
    private $facilityTypeRepository;
    /** @var VehicleClassRepository */
    private $vehicleClassRepository;
    /** @var AuthorisationForTestingMotAtSiteStatusRepository */
    private $authForTestingMotStatusRepository;
    /** @var SiteTestingDailyScheduleRepository */
    private $siteTestingDailyScheduleRepository;
    /** @var NonWorkingDayCountryRepository */
    private $nonWorkingDayCountryRepository;
    /** @var SiteStatusRepository */
    private $siteStatusRepository;
    /** @var VtsMapper */
    private $vtsMapper;
    /** @var XssFilter */
    private $xssFilter;
    /** @var UpdateVtsAssertion */
    private $updateVtsAssertion;
    /** @var SiteValidator */
    private $validator;
    /** @var DateTimeHolder */
    private $dateTimeHolder;

    /**
     * @param EntityManager                                    $entityManager
     * @param AuthorisationServiceInterface                    $authService
     * @param MotIdentityInterface                             $motIdentity
     * @param ContactDetailsService                            $contactService
     * @param EventService                                     $eventService
     * @param SiteRiskAssessmentRepository                     $siteRiskAssessmentRepository
     * @param SiteTypeRepository                               $siteTypeRepository
     * @param SiteRepository                                   $repository
     * @param SiteContactTypeRepository                        $siteContactTypeRepository
     * @param BrakeTestTypeRepository                          $brakeTestTypeRepository
     * @param FacilityTypeRepository                           $facilityTypeRepository
     * @param VehicleClassRepository                           $vehicleClassRepository
     * @param AuthorisationForTestingMotAtSiteStatusRepository $authForTestingMotStatusRepository
     * @param SiteTestingDailyScheduleRepository               $siteTestingDailyScheduleRepository
     * @param NonWorkingDayCountryRepository                   $nonWorkingDayCountryRepository
     * @param SiteStatusRepository                             $siteStatusRepository
     * @param XssFilter                                        $xssFilter
     * @param SiteBusinessRoleMapMapper                        $positionMapper
     * @param UpdateVtsAssertion                               $updateVtsAssertion
     * @param Hydrator                                         $objectHydrator
     * @param SiteValidator                                    $validator
     */
    public function __construct(
        EntityManager $entityManager,
        AuthorisationServiceInterface $authService,
        MotIdentityInterface $motIdentity,
        ContactDetailsService $contactService,
        EventService $eventService,
        SiteRiskAssessmentRepository $siteRiskAssessmentRepository,
        SiteTypeRepository $siteTypeRepository,
        SiteRepository $repository,
        SiteContactTypeRepository $siteContactTypeRepository,
        BrakeTestTypeRepository $brakeTestTypeRepository,
        FacilityTypeRepository $facilityTypeRepository,
        VehicleClassRepository $vehicleClassRepository,
        AuthorisationForTestingMotAtSiteStatusRepository $authForTestingMotStatusRepository,
        SiteTestingDailyScheduleRepository $siteTestingDailyScheduleRepository,
        NonWorkingDayCountryRepository $nonWorkingDayCountryRepository,
        SiteStatusRepository $siteStatusRepository,
        XssFilter $xssFilter,
        SiteBusinessRoleMapMapper $positionMapper,
        UpdateVtsAssertion $updateVtsAssertion,
        Hydrator $objectHydrator,
        SiteValidator $validator
    ) {
        parent::__construct($entityManager);

        $this->authService = $authService;
        $this->identity = $motIdentity;
        $this->contactService = $contactService;
        $this->eventService = $eventService;

        $this->repository = $repository;
        $this->siteRiskAssessmentRepository = $siteRiskAssessmentRepository;
        $this->siteTypeRepository = $siteTypeRepository;
        $this->brakeTestTypeRepository = $brakeTestTypeRepository;
        $this->siteContactTypeRepository = $siteContactTypeRepository;
        $this->facilityTypeRepository = $facilityTypeRepository;
        $this->vehicleClassRepository = $vehicleClassRepository;
        $this->authForTestingMotStatusRepository = $authForTestingMotStatusRepository;
        $this->siteTestingDailyScheduleRepository = $siteTestingDailyScheduleRepository;
        $this->nonWorkingDayCountryRepository = $nonWorkingDayCountryRepository;
        $this->siteStatusRepository = $siteStatusRepository;

        $this->xssFilter = $xssFilter;
        $this->positionMapper = $positionMapper;
        $this->updateVtsAssertion = $updateVtsAssertion;
        $this->objectHydrator = $objectHydrator;

        $this->vtsMapper = new VtsMapper();

        $this->dateTimeHolder = new DateTimeHolder();
        $this->validator = $validator;
    }

    /**
     * @param VehicleTestingStationDto $dto
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function create(VehicleTestingStationDto $dto)
    {
        $this->authService->assertGranted(PermissionInSystem::VEHICLE_TESTING_STATION_CREATE);

        /** @var VehicleTestingStationDto $dto */
        $dto = $this->xssFilter->filter($dto);

        // form for create vts dont have status field
        $dto->setStatus(SiteStatusCode::APPROVED);
        $this->validator->validate($dto);

        if ($dto->isNeedConfirmation() === true) {
            return null;
        }

        //  logical block :: create site
        /** @var SiteType $siteType */
        $siteType = $this->siteTypeRepository->getByCode($dto->getType());
        /** @var BrakeTestType $brakeTestType */
        $brakeTestType = $this->brakeTestTypeRepository->getByCode(BrakeTestTypeCode::ROLLER);

        //  generate site number
        $siteNumber = $this->repository->getNextSiteNumber();

        $site = new Site();

        // Default status for Site is Approved only on creation (see: Alisdar Cameron)
        $approvedStatus = SiteStatusCode::APPROVED;
        $status = $this->siteStatusRepository->getByCode($approvedStatus);

        if (!$status) {
            throw new NotFoundException('SiteStatusCode', $approvedStatus);
        }

        $site->setStatus($status);
        $site->setStatusChangedOn(new \DateTime());

        $site
            ->setName(empty($dto->getName()) ? $siteNumber : $dto->getName())
            ->setSiteNumber($siteNumber)
            ->setType($siteType)
            ->setDualLanguage($dto->isDualLanguage())
            ->setScottishBankHoliday($dto->isScottishBankHoliday())
            ->setDefaultBrakeTestClass1And2($brakeTestType)
            ->setDefaultServiceBrakeTestClass3AndAbove($brakeTestType)
            ->setDefaultParkingBrakeTestClass3AndAbove($brakeTestType);

        $nonWorkingDayCountry = $this->getNonWorkingDayCountry(
            $dto->isScottishBankHoliday() ? CountryCode::SCOTLAND : CountryCode::ENGLAND
        );
        $site->setNonWorkingDayCountry($nonWorkingDayCountry);

        //  logical block :: create contacts
        /** @var SiteContactDto $contactDto */
        foreach ($dto->getContacts() as $contactDto) {
            /** @var SiteContactType $contactType */
            $contactType = $this->siteContactTypeRepository->getByCode($contactDto->getType());

            $contactDetails = $this->contactService->setContactDetailsFromDto(
                $contactDto, new ContactDetail()
            );

            $site->setContact($contactDetails, $contactType);
        }

        //  logical block :: create event
        //  (should be located before any persist, because somebody put flush in addEvent method)
        $this->createSiteEvent(
            $site,
            EventTypeCode::DVSA_ADMINISTRATOR_CREATE_SITE,
            sprintf(
                EventDescription::DVSA_ADMINISTRATOR_CREATE_SITE,
                $siteNumber,
                $site->getName(),
                $this->getUserName()
            )
        );

        //  logical block :: create facilities
        if (!empty($dto->getFacilities())) {
            $this->createSiteFacility($site, $dto);
        }

        //  logical block :: create authorisation
        if (!empty($dto->getTestClasses())) {
            $this->createAuthorisationForTestingMotAtSite(
                $site,
                $dto,
                AuthorisationForTestingMotAtSiteStatusCode::APPROVED
            );
        }

        //  logical block :: create default opening hours
        $this->mapDefaultOpenHoursTesting($site);

        //  logical block :: store in db
        $this->repository->save($site);

        return [
            'id' => $site->getId(),
            'siteNumber' => $site->getSiteNumber(),
        ];
    }

    /**
     * Create the default opening/closing time for a site
     * Monday->Friday
     * 9am->5pm.
     *
     * @param Site $site
     * @param Time $openTime
     * @param Time $closeTime
     */
    private function mapDefaultOpenHoursTesting(Site $site, $openTime = null, $closeTime = null)
    {
        $openTime = $openTime === null ? new Time(9, 0, 0) : $openTime;
        $closeTime = $closeTime === null ? new Time(17, 0, 0) : $closeTime;

        for ($weekday = self::MONDAY; $weekday <= self::FRIDAY; ++$weekday) {
            $this->siteTestingDailyScheduleRepository->createOpeningHours($site, $weekday, $openTime, $closeTime);
        }
        $this->siteTestingDailyScheduleRepository->createOpeningHours($site, self::SATURDAY, null, null);
        $this->siteTestingDailyScheduleRepository->createOpeningHours($site, self::SUNDAY, null, null);
    }

    /**
     * Create the Site Facilities relation to the site.
     *
     * @param Site                     $site
     * @param VehicleTestingStationDto $dto
     *
     * @throws NotFoundException
     */
    private function createSiteFacility(Site $site, VehicleTestingStationDto $dto)
    {
        $facilities = [];
        /** @var FacilityDto $facility */
        foreach ($dto->getFacilities() as $facility) {
            /** @var FacilityType $type */
            $type = $this->facilityTypeRepository->getByCode($facility->getType()->getCode());

            $siteFacility = new SiteFacility();
            $siteFacility
                ->setFacilityType($type)
                ->setName($type->getName())
                ->setVehicleTestingStation($site);

            $this->entityManager->persist($siteFacility);

            $facilities[] = $siteFacility;
        }

        if (!empty($facilities)) {
            $site->setFacilities($facilities);
        }
    }

    /**
     * Create the Site authorisation and classes in relation to the site.
     *
     * @param Site                     $site
     * @param VehicleTestingStationDto $dto
     * @param string                   $status
     *
     * @throws NotFoundException
     */
    private function createAuthorisationForTestingMotAtSite(Site $site, VehicleTestingStationDto $dto, $status)
    {
        foreach ($dto->getTestClasses() as $testClass) {
            /** @var VehicleClass $vehicleClass */
            $vehicleClass = $this->vehicleClassRepository->getByCode($testClass);
            /** @var AuthorisationForTestingMotAtSiteStatus $authStatus */
            $authStatus = $this->authForTestingMotStatusRepository->getByCode($status);

            $auth = (new AuthorisationForTestingMotAtSite())
                ->setVehicleClass($vehicleClass)
                ->setSite($site)
                ->setStatus($authStatus);

            $this->entityManager->persist($vehicleClass);
            $this->entityManager->persist($auth);

            $site->addAuthorisationsForTestingMotAtSite($auth);
        }
    }

    /**
     * Create site event.
     *
     * @param Site   $site
     * @param string $eventType
     * @param string $eventDesc
     *
     * @throws \Exception
     */
    private function createSiteEvent(Site $site, $eventType, $eventDesc)
    {
        $event = $this->eventService->addEvent(
            $eventType,
            $eventDesc,
            $this->dateTimeHolder->getCurrent(true)
        );

        $eventMap = (new EventSiteMap())
            ->setEvent($event)
            ->setSite($site);

        $this->entityManager->persist($eventMap);
    }

    /**
     * @param $id
     *
     * @return VehicleTestingStationDto
     *
     * @throws NotFoundException
     */
    public function getSite($id)
    {
        $this->authService->assertGrantedAtSite(PermissionAtSite::VEHICLE_TESTING_STATION_READ, $id);

        /** @var Site $site */
        $site = $this->repository->find((int) $id);
        if ($site === null) {
            throw new NotFoundException('Site', $id);
        }

        $lastAssessment = $site->getLastSiteAssessment();
        $organisationId = (!empty($lastAssessment)) ? $lastAssessment->getAeOrganisationId() : null;
        $siteAssessments = $this->siteRiskAssessmentRepository->getLatestAssessmentsForSite($id, $organisationId, 2);

        return $this->vtsMapper->toDtoWithLatestAssessments($site, $siteAssessments);
    }

    /**
     * Used when tester gets information for site where demo test should happen.
     *
     * @param $siteNumber
     */
    public function getSiteByNumber($siteNumber)
    {
    }

    public function getSiteName($id)
    {
        $this->authService->assertGrantedAtSite(PermissionAtSite::VEHICLE_TESTING_STATION_READ, $id);

        /** @var Site $site */
        $site = $this->repository->get((int) $id);

        return $site->getName();
    }

    public function getSiteAuthorisedClasses($siteId)
    {
        $approvedVehicleClasses = $this->extractApprovedVehicleClasses($this->getSite($siteId));

        return $approvedVehicleClasses;
    }

    /**
     * Fetch a single Vehicle Testing Station by site number
     * - an alternative to getting it by ID.
     *
     * @param $siteNumber
     *
     * @return VehicleTestingStationDto
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getSiteBySiteNumber($siteNumber)
    {
        /** @var Site $site */
        $site = $this->repository->findOneBy(['siteNumber' => strtoupper(trim($siteNumber))]);
        if (!$site) {
            throw new NotFoundException('Site', $siteNumber);
        }

        $this->authService->assertGrantedAtSite(
            PermissionAtSite::VEHICLE_TESTING_STATION_READ, $site->getId()
        );

        return $this->vtsMapper->toDto($site);
    }

    /**
     * @param $countryCode
     *
     * @return NonWorkingDayCountry
     */
    private function getNonWorkingDayCountry($countryCode)
    {
        return $this->nonWorkingDayCountryRepository->getOneByCode($countryCode);
    }

    /**
     * @param VehicleTestingStationDto $site
     *
     * @return array
     */
    private function extractApprovedVehicleClasses($site)
    {
        $vtsApprovedClasses = $site->getTestClasses();

        $result = [];
        $allClasses = VehicleClassCode::getAll();
        foreach ($allClasses as $class) {
            $result['class'.$class] = in_array($class, $vtsApprovedClasses)
                ? AuthorisationForTestingMotStatusCode::QUALIFIED
                : null;
        }

        return $result;
    }

    /**
     * @return int
     * @description return ID from zend identity
     */
    private function getUserName()
    {
        return $this->identity->getUsername();
    }
}
