<?php

namespace SiteApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\Assertion\UpdateVtsAssertion;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\AuthorisationForTestingMotAtSiteStatusCode;
use DvsaCommon\Enum\CountryCode;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Model\VehicleTestingStation;
use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Filter\XssFilter;
use DvsaEntities\Entity\AuthorisationForTestingMotAtSite;
use DvsaEntities\Entity\AuthorisationForTestingMotAtSiteStatus;
use DvsaEntities\Entity\EventSiteMap;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteStatus;
use DvsaEntities\Entity\SiteType;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Repository\AuthorisationForTestingMotAtSiteStatusRepository;
use DvsaEntities\Repository\NonWorkingDayCountryRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\SiteStatusRepository;
use DvsaEntities\Repository\SiteTypeRepository;
use DvsaEntities\Repository\VehicleClassRepository;
use DvsaEventApi\Service\EventService;
use SiteApi\Service\Validator\SiteDetailsValidator;
use SiteApi\Service\Validator\SiteValidator;

class SiteDetailsService
{
    const NAME_FIELD = 'Name';
    const STATUS_FIELD = 'Status';
    const CLASSES_FIELD = 'Testing classes';
    const AREA_OFFICE_FIELD = 'area_office';
    const TYPE_FIELD = 'type';
    const OLD_VALUE = 'old';
    const NEW_VALUE = 'new';

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
    /**
     * @var VehicleClassRepository
     */
    private $vehicleClassRepository;
    /**
     * @var AuthorisationForTestingMotAtSiteStatusRepository
     */
    private $authForTestingMotStatusRepository;
    /**
     * @var SiteStatusRepository
     */
    private $siteStatusRepository;
    /**
     * @var SiteDetailsValidator
     */
    private $siteDetailsValidator;
    /**
     * @var SiteTypeRepository
     */
    private $siteTypeRepository;
    /**
     * @var NonWorkingDayCountryRepository
     */
    private $nonWorkingDayCountryRepository;

    public function __construct(
        SiteRepository $siteRepository,
        MotAuthorisationServiceInterface $authService,
        UpdateVtsAssertion $updateVtsAssertion,
        XssFilter $xssFilter,
        SiteValidator $siteValidator,
        EventService $eventService,
        MotIdentityInterface $identity,
        EntityManager $entityManager,
        VehicleClassRepository $vehicleClassRepository,
        AuthorisationForTestingMotAtSiteStatusRepository $authForTestingMotStatusRepository,
        SiteStatusRepository $siteStatusRepository,
        SiteDetailsValidator $siteDetailsValidator,
        SiteTypeRepository $siteTypeRepository,
        NonWorkingDayCountryRepository $nonWorkingDayCountryRepository
    ) {
        $this->siteRepository = $siteRepository;
        $this->authService = $authService;
        $this->updateVtsAssertion = $updateVtsAssertion;
        $this->xssFilter = $xssFilter;
        $this->siteValidator = $siteValidator;
        $this->eventService = $eventService;
        $this->entityManager = $entityManager;

        $this->dateTimeHolder = new DateTimeHolder();
        $this->identity = $identity;
        $this->vehicleClassRepository = $vehicleClassRepository;
        $this->authForTestingMotStatusRepository = $authForTestingMotStatusRepository;
        $this->siteStatusRepository = $siteStatusRepository;
        $this->siteDetailsValidator = $siteDetailsValidator;
        $this->siteTypeRepository = $siteTypeRepository;
        $this->nonWorkingDayCountryRepository = $nonWorkingDayCountryRepository;
    }

    /**
     * @param int   $siteId
     * @param array $data
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     * @throws \DvsaCommon\Exception\UnauthorisedException
     */
    public function patch($siteId, array $data)
    {
        $dto = DtoHydrator::jsonToDto($data);

        /** @var VehicleTestingStationDto $dto */
        $dto = $this->xssFilter->filter($dto);

        $site = $this->siteRepository->get($siteId);
        $diff = [];

        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_NAME, $data)) {
            $this->authService->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_NAME, $siteId);
            $this->siteDetailsValidator->validateName($dto);
        }
        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_CLASSES, $data)) {
            $this->authService->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_CLASSES, $siteId);
            $this->siteDetailsValidator->validateTestClasses($dto);
        }
        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_STATUS, $data)) {
            $this->authService->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_STATUS, $siteId);
            $this->siteDetailsValidator->validateStatus($dto);
        }
        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_TYPE, $data)) {
            $this->authService->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_TYPE, $siteId);
            $this->siteDetailsValidator->validateType($dto);
        }

        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_COUNTRY, $data)) {
            $this->authService->assertGrantedAtSite(PermissionAtSite::VTS_UPDATE_COUNTRY, $siteId);
            $this->siteDetailsValidator->validateCountry($dto);
        }

        $this->siteDetailsValidator->getErrors()->throwIfAnyField();

        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_NAME, $data)) {
            $this->updateName($site, $dto, $diff);
        }
        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_CLASSES, $data)) {
            $this->updateClasses($site, $dto, $diff);
        }
        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_STATUS, $data)) {
            $this->updateStatus($site, $dto, $diff);
        }
        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_TYPE, $data)) {
            $this->updateType($site, $dto, $diff);
        }
        if (array_key_exists(VehicleTestingStation::PATCH_PROPERTY_COUNTRY, $data)) {
            $this->updateCountry($site, $dto);
        }

        $this->raiseSiteEvents($site, $diff);

        $this->siteRepository->persist($site);
        $this->siteRepository->flush();

        return [
            'success' => true,
        ];
    }

    private function updateName(Site $site, VehicleTestingStationDto $dto, array &$diff)
    {
        $old = $site->getName();
        $new = $dto->getName();

        if ($old !== $new) {
            $site->setName($new);
            $this->createDiffArray($diff, $old, $new, self::NAME_FIELD);
        }
    }

    private function updateType(Site $site, VehicleTestingStationDto $dto, array &$diff)
    {
        $old = $site->getType();
        $new = $dto->getType();

        if (!empty($new) && $new != $old->getCode()) {
            /** @var SiteType $newType */
            $newType = $this->siteTypeRepository->getByCode($new);
            $site->setType($newType);
            $this->createDiffArray($diff, $old->getName(), $newType->getName(), self::TYPE_FIELD);
        }
    }

    private function updateStatus(Site $site, VehicleTestingStationDto $dto, array &$diff)
    {
        /** @var SiteStatus $old */
        $old = $site->getStatus();

        /** @var string $new */
        $new = $dto->getStatus();

        if (!empty($new) && $new !== $old->getCode()) {
            $newStatus = $this->siteStatusRepository->getByCode($new);
            $site->setStatus($newStatus);
            $site->setStatusChangedOn(new \DateTime());
            $this->createDiffArray($diff, $old->getName(), $newStatus->getName(), self::STATUS_FIELD);
        }
    }

    private function updateClasses(Site $site, VehicleTestingStationDto $dto, array &$diff)
    {
        $oldClasses = $site->getApprovedVehicleClasses();
        $new = (array) $dto->getTestClasses();

        if ($this->isTestClassesModified($oldClasses, $new)) {
            $oldEntities = $site->getApprovedAuthorisationForTestingMotAtSite();
            $stringifyOldClasses = $this->replaceEmptyTextWithNone(
                implode(', ', $this->transformClassEntitiesToClassCodesArray($oldClasses))
            );
            $stringifyNewClasses = $this->replaceEmptyTextWithNone(implode(', ', $new));

            $this->removeTestClasses($site, $oldEntities);
            $this->createAuthorisationForTestingMotAtSite(
                $site,
                $dto,
                AuthorisationForTestingMotAtSiteStatusCode::APPROVED
            );

            $this->createDiffArray($diff, $stringifyOldClasses, $stringifyNewClasses, self::CLASSES_FIELD);
        }
    }

    private function updateCountry(Site $site, VehicleTestingStationDto $dto)
    {
        $country = $dto->getCountry();
        switch ($country) {
            case CountryCode::WALES:
                $site->setDualLanguage(true);
                $site->setScottishBankHoliday(false);
                break;
            case CountryCode::SCOTLAND:
                $site->setDualLanguage(false);
                $site->setScottishBankHoliday(true);
                break;
            case CountryCode::ENGLAND:
                $site->setDualLanguage(false);
                $site->setScottishBankHoliday(false);
                break;
        }

        $site->setNonWorkingDayCountry($this->nonWorkingDayCountryRepository->getOneByCode($country));
    }

    /**
     * @param AuthorisationForTestingMotAtSite[] $oldClasses
     * @param array                              $newClasses
     *
     * @return bool
     */
    private function isTestClassesModified(array $oldClasses, array $newClasses)
    {
        $oldClassCodes = $this->transformClassEntitiesToClassCodesArray($oldClasses);

        return $oldClassCodes !== $newClasses;
    }

    /**
     * @param Site                               $site
     * @param AuthorisationForTestingMotAtSite[] $toRemove
     */
    private function removeTestClasses(Site $site, array $toRemove)
    {
        /** @var AuthorisationForTestingMotAtSite $oldClass */
        foreach ($toRemove as $oldClass) {
            $site->removeAuthorisationForTestingMotAtSite($oldClass);
            $this->entityManager->remove($oldClass);
        }
        $this->entityManager->persist($site);
    }

    /**
     * @param Site  $site
     * @param array $changesDiff
     */
    private function raiseSiteEvents(Site $site, array $changesDiff)
    {
        foreach ($changesDiff as $fieldName => $diffArray) {
            $this->createSiteEvent(
                $site,
                EventTypeCode::DVSA_ADMINISTRATOR_UPDATE_SITE,
                sprintf(
                    EventDescription::DVSA_ADMINISTRATOR_UPDATE_SITE,
                    $fieldName,
                    $changesDiff[$fieldName][self::OLD_VALUE],
                    $changesDiff[$fieldName][self::NEW_VALUE],
                    $site->getSiteNumber(),
                    $site->getName(),
                    $this->getUserName()
                )
            );
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
     * @param array $diff
     * @param $old
     * @param $new
     * @param $fieldName
     */
    private function createDiffArray(array &$diff, $old, $new, $fieldName)
    {
        $diff[$fieldName] = [
            self::OLD_VALUE => $old,
            self::NEW_VALUE => $new,
        ];
    }

    /**
     * @return int
     * @description return ID from zend identity
     */
    private function getUserName()
    {
        return $this->identity->getUsername();
    }

    /**
     * Create the Site authorisation and classes in relation to the site.
     *
     * @param Site                     $site
     * @param VehicleTestingStationDto $dto
     * @param string                   $status
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

            $this->entityManager->persist($site);
        }
    }

    /**
     * @param AuthorisationForTestingMotAtSite[] $classEntities
     *
     * @return array Array of vehicle class codes
     */
    private function transformClassEntitiesToClassCodesArray(array $classEntities)
    {
        $classCodeArray = array_map(
            function (VehicleClass $oldClass) {
                return $oldClass->getCode();
            },
            $classEntities
        );

        return $classCodeArray;
    }

    /**
     * Returns original text, or "none" if passed text was empty.
     *
     * @param string $text
     *
     * @return string
     */
    protected function replaceEmptyTextWithNone($text)
    {
        if (empty($text)) {
            $text = 'none';
        }

        return $text;
    }
}
