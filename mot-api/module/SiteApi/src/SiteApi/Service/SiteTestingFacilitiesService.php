<?php

namespace SiteApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\Assertion\UpdateVtsAssertion;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Constants\FacilityTypeCode;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Dto\Site\FacilityDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommonApi\Filter\XssFilter;
use DvsaEntities\Entity\EventSiteMap;
use DvsaEntities\Entity\FacilityType;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteFacility;
use DvsaEntities\Repository\FacilityTypeRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEventApi\Service\EventService;
use SiteApi\Service\Validator\SiteValidator;
use DvsaCommon\Utility\ArrayUtils;

class SiteTestingFacilitiesService
{
    const FACILITY_COUNT_OLD = 'old';
    const FACILITY_COUNT_NEW = 'new';

    /**
     * @var SiteRepository
     */
    private $siteRepository;
    /**
     * @var AuthorisationServiceInterface
     */
    private $authService;
    /**
     * @var UpdateVtsAssertion
     */
    private $updateVtsAssertion;
    /**
     * @var XssFilter
     */
    private $xssFilter;
    /**
     * @var SiteValidator
     */
    private $siteValidator;
    /**
     * @var EventService
     */
    private $eventService;
    /**
     * @var FacilityTypeRepository
     */
    private $facilityTypeRepository;
    /**
     * @var DateTimeHolder
     */
    private $dateTimeHolder;
    /**
     * @var MotIdentityInterface
     */
    private $identity;
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        SiteRepository $siteRepository,
        MotAuthorisationServiceInterface $authService,
        UpdateVtsAssertion $updateVtsAssertion,
        XssFilter $xssFilter,
        SiteValidator $siteValidator,
        EventService $eventService,
        FacilityTypeRepository $facilityTypeRepository,
        MotIdentityInterface $identity,
        EntityManager $entityManager
    ) {
        $this->siteRepository = $siteRepository;
        $this->authService = $authService;
        $this->updateVtsAssertion = $updateVtsAssertion;
        $this->xssFilter = $xssFilter;
        $this->siteValidator = $siteValidator;
        $this->eventService = $eventService;
        $this->facilityTypeRepository = $facilityTypeRepository;
        $this->entityManager = $entityManager;

        $this->dateTimeHolder = new DateTimeHolder();
        $this->identity = $identity;
    }

    public function get($siteId)
    {
        $this->authService->assertGranted(PermissionInSystem::VEHICLE_TESTING_STATION_LIST);

        $site = $this->siteRepository->get($siteId);
        $facilities = $site->getFacilities();

        return $facilities;
    }

    /**
     * The update method checks the following permissions using the updateVtsAssertion:.
     *
     * PermissionAtSite::VTS_UPDATE_TESTING_FACILITIES_DETAILS,
     * PermissionAtSite::VTS_UPDATE_NAME,
     * PermissionAtSite::VTS_UPDATE_CORRESPONDENCE_DETAILS,
     * PermissionAtSite::VTS_UPDATE_BUSINESS_DETAILS,
     * PermissionAtSite::VTS_UPDATE_SITE_DETAILS
     *
     * @param $siteId
     * @param VehicleTestingStationDto $data
     *
     * @return array|null
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     * @throws \DvsaCommon\Exception\UnauthorisedException
     */
    public function update($siteId, VehicleTestingStationDto $data)
    {
        $this->updateVtsAssertion->assertUpdateTestingFacilities($siteId);

        /** @var VehicleTestingStationDto $data */
        $data = $this->xssFilter->filter($data);
        $this->siteValidator->validateFacilities($data);

        if ($data->isNeedConfirmation() === true) {
            return true;
        }

        $site = $this->siteRepository->get($siteId);
        /** @var SiteFacility[] $originalFacilities */
        $originalFacilities = $site->getFacilities();
        /** @var FacilityDto[] $newFacilities */
        $newFacilities = $data->getFacilities();

        $facilitiesCount = $this->facilitiesCount($originalFacilities, $newFacilities, true);
        // Do the update in db
        $this->updateFacilities($originalFacilities, $newFacilities, $site);

        $this->raiseSiteEvents($site, $facilitiesCount);

        return [
            'success' => true,
        ];
    }

    /**
     * @param FacilityDto[] $data
     *
     * @return array|SiteFacility[]
     */
    private function transformDtosToEntities($data, Site $site)
    {
        $newFacilities = array_map(function (FacilityDto $facilityDto) use ($site) {
            /** @var FacilityType $type */
                $type = $this->facilityTypeRepository->getByCode($facilityDto->getType()->getCode());

            return (new SiteFacility())
                    ->setFacilityType($type)
                    ->setVehicleTestingStation($site)
                    ->setName($type->getName())
                    ;
        },
            $data
        );

        return $newFacilities;
    }

    /**
     * @param SiteFacility[] $originalFacilities
     */
    private function removeFacilities($originalFacilities, $doFlush = true)
    {
        foreach ($originalFacilities as $siteFacility) {
            $this->entityManager->remove($siteFacility);
        }

        if (true === $doFlush) {
            $this->entityManager->flush();
        }
    }

    /**
     * @param SiteFacility[] $newFacilities
     */
    private function saveFacilities($newFacilities, $doFlush = true)
    {
        array_walk($newFacilities, function (SiteFacility $siteFacility) {
            $this->entityManager->persist($siteFacility);
        });

        if (true === $doFlush) {
            $this->entityManager->flush();
        }
    }

    /**
     * @param Site  $site
     * @param array $facilitiesCount
     */
    private function raiseSiteEvents(Site $site, array $facilitiesCount)
    {
        /** @var array $oldValues */
        $oldValues = $facilitiesCount[self::FACILITY_COUNT_OLD];
        /** @var array $newValues */
        $newValues = $facilitiesCount[self::FACILITY_COUNT_NEW];

        foreach ($newValues as $typeCode => $value) {
            $fieldName = $typeCode;
            $oldValue = ArrayUtils::tryGet($oldValues, $typeCode, 0);
            $newValue = ArrayUtils::tryGet($newValues, $typeCode, 0);

            if ($newValue === $oldValue) {
                continue;
            }

            $this->createSiteEvent(
                $site,
                EventTypeCode::DVSA_ADMINISTRATOR_UPDATE_SITE,
                sprintf(
                    EventDescription::DVSA_ADMINISTRATOR_UPDATE_SITE,
                    $fieldName,
                    $oldValue,
                    $newValue,
                    $site->getSiteNumber(),
                    $site->getName(),
                    $this->getUserName()
                )
            );
        }

        $this->entityManager->flush();
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
     * Counts facilities grouped by facility type.
     *
     * @param SiteFacility[] $originalFacilities
     * @param FacilityDto[]  $newFacilities
     *
     * @return array
     */
    private function facilitiesCount($originalFacilities, $newFacilities, $mergeAtlAndOptl = true)
    {
        $result = [
            self::FACILITY_COUNT_OLD => $this->countOriginalFacilities($originalFacilities),
            self::FACILITY_COUNT_NEW => $this->countNewFacilities($newFacilities),
        ];

        if (true === $mergeAtlAndOptl) {
            $result = $this->mergeAtlAndOptlCounts($result);
        }

        return $result;
    }

    /**
     * Merges ATL and OPTL keys of array containing counts into one value under one key (OPTL).
     *
     * @param array $facilitiesCount
     *
     * @return array
     */
    private function mergeAtlAndOptlCounts(array $facilitiesCount)
    {
        $result = [
            self::FACILITY_COUNT_OLD => $facilitiesCount[self::FACILITY_COUNT_OLD],
            self::FACILITY_COUNT_NEW => [],
        ];

        $dataType = self::FACILITY_COUNT_NEW;

        $atl = ArrayUtils::tryGet(
            $facilitiesCount[$dataType],
            FacilityTypeCode::AUTOMATED_TEST_LANE,
            0
        );
        $optl = ArrayUtils::tryGet(
            $facilitiesCount[$dataType],
            FacilityTypeCode::ONE_PERSON_TEST_LANE,
            0
        );
        $tptl = ArrayUtils::tryGet(
            $facilitiesCount[$dataType],
            FacilityTypeCode::TWO_PERSON_TEST_LANE,
            0
        );

        $result[$dataType][FacilityTypeCode::ONE_PERSON_TEST_LANE] = $atl + $optl;
        $result[$dataType][FacilityTypeCode::TWO_PERSON_TEST_LANE] = $tptl;

        return $result;
    }

    /**
     * @param SiteFacility[] $originalFacilities
     *
     * @return array
     */
    private function countOriginalFacilities($originalFacilities)
    {
        $result = [];

        if ($originalFacilities) {
            foreach ($originalFacilities as $siteFacility) {
                $typeCode = $siteFacility->getFacilityType()->getCode();
                if (!isset($result[$typeCode])) {
                    $result[$typeCode] = 0;
                }
                ++$result[$typeCode];
            }
        }

        return $result;
    }

    /**
     * @param FacilityDto[] $newFacilities
     *
     * @return array
     */
    private function countNewFacilities($newFacilities)
    {
        $result = [];

        array_walk($newFacilities, function (FacilityDto $facilityDto) use (&$result) {
            $typeCode = $facilityDto->getType()->getCode();
            if (!isset($result[$typeCode])) {
                $result[$typeCode] = 0;
            }
            ++$result[$typeCode];
        });

        return $result;
    }

    /**
     * @param SiteFacility[] $originalFacilities
     * @param FacilityDto[]  $newFacilities
     * @param Site           $site
     */
    private function updateFacilities($originalFacilities, $newFacilities, Site $site)
    {
        // del old ones and replace them with new ones ? siteFacility => value object ? not connected ?
        $this->removeFacilities($originalFacilities, false);
        $newFacilities = $this->transformDtosToEntities($newFacilities, $site);
        $this->saveFacilities($newFacilities, true);
    }

    private function getUserName()
    {
        return $this->identity->getUsername();
    }
}
