<?php

namespace DataCatalogApi\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Mapper\ReasonForCancelMapper;
use DvsaCommonApi\Service\Mapper\ReasonForRefusalMapper;
use DvsaEntities\Entity\AuthForAeStatus;
use DvsaEntities\Entity\AuthorisationForTestingMotStatus;
use DvsaEntities\Entity\BrakeTestType;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\CompanyType;
use DvsaEntities\Entity\Country;
use DvsaEntities\Entity\CountryOfRegistration;
use DvsaEntities\Entity\EmptyVinReason;
use DvsaEntities\Entity\EmptyVrmReason;
use DvsaEntities\Entity\EnforcementDecision;
use DvsaEntities\Entity\EnforcementDecisionCategory;
use DvsaEntities\Entity\EnforcementDecisionOutcome;
use DvsaEntities\Entity\EnforcementDecisionReinspectionOutcome;
use DvsaEntities\Entity\EnforcementDecisionScore;
use DvsaEntities\Entity\EnforcementVisitOutcome;
use DvsaEntities\Entity\EquipmentModelStatus;
use DvsaEntities\Entity\FuelType;
use DvsaEntities\Entity\MotTestReasonForCancel;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\PersonSystemRole;
use DvsaEntities\Entity\ReasonForRefusal;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteStatus;
use DvsaEntities\Entity\SiteType;
use DvsaEntities\Entity\TransmissionType;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Entity\VisitReason;
use DvsaEntities\Repository\ColourRepository;
use DvsaEntities\Repository\FuelTypeRepository;
use DvsaEntities\Repository\SiteStatusRepository;
use DvsaEntities\Entity\EventTypeOutcomeCategoryMap;

/**
 * Class DataCatalogService.
 */
class DataCatalogService extends AbstractService
{
    const ENUM_TYPE_STANDARD = 1;
    const ENUM_TYPE_DVLA = 2;

    private $objectHydrator;
    private $authService;

    public function __construct(
        EntityManager $entityManager,
        DoctrineObject $objectHydrator,
        AuthorisationServiceInterface $authService
    ) {
        parent::__construct($entityManager);
        $this->objectHydrator = $objectHydrator;
        $this->authService = $authService;
    }

    public function getEnforcementDecisionData()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(EnforcementDecision::class)
            ->findBy([], ['position' => 'ASC']);

        return $this->checkAndExtractItems($items, 'EnforcementDecision');
    }

    public function getEnforcementDecisionCategoryData()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(EnforcementDecisionCategory::class)
            ->findBy([], ['position' => 'ASC']);

        return $this->checkAndExtractItems($items, 'EnforcementDecisionCategory');
    }

    public function getEnforcementDecisionOutcomeData()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(EnforcementDecisionOutcome::class)
            ->findBy([], ['position' => 'ASC']);

        return $this->checkAndExtractItems($items, 'EnforcementDecisionOutcome');
    }

    public function getEnforcementDecisionScoreData()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(EnforcementDecisionScore::class)
            ->findBy([], ['position' => 'ASC']);

        return $this->checkAndExtractItems($items, 'EnforcementDecisionScore');
    }

    public function getEnforcementDecisionReinspectionOutcomeData()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(EnforcementDecisionReinspectionOutcome::class)
            ->findBy([], ['position' => 'ASC']);

        return $this->checkAndExtractItems($items, 'EnforcementDecisionReinspectionOutcome');
    }

    public function getSiteAssessmentVisitOutcomeData()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(EnforcementVisitOutcome::class)
            ->findBy([], ['position' => 'ASC']);

        return $this->checkAndExtractItems($items, 'EnforcementVisitOutcome');
    }

    public function getReasonForSiteVisitData()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(VisitReason::class)
            ->findBy([], ['position' => 'ASC']);

        return $this->checkAndExtractItems($items, 'VisitReason');
    }

    public function getMotTestReasonsForCancel(array $criteria = [])
    {
        $items = $this->entityManager->getRepository(MotTestReasonForCancel::class)->findBy($criteria);

        return (new ReasonForCancelMapper())->manyToDto($items);
    }

    public function getColours()
    {
        /** @var ColourRepository $repo */
        $repo = $this->entityManager->getRepository(Colour::class);
        $items = $repo->getAll();

        return $this->extractType2EnumValues($items);
    }

    public function getFuelTypes()
    {
        /** @var FuelTypeRepository $repo */
        $repo = $this->entityManager->getRepository(FuelType::class);
        $items = $repo->getAll();

        return $this->extractType2EnumValues($items);
    }

    public function getDemoTestResultData()
    {
        return [
            0 => 'Satisfactory',
            1 => 'Unsatisfactory',
        ];
    }

    public function getReasonsForRefusal(array $criteria = [])
    {
        $items = $this->entityManager->getRepository(ReasonForRefusal::class)->findBy($criteria, ['id' => 'ASC']);

        return (new ReasonForRefusalMapper())->manyToDto($items);
    }

    public function getVehicleClasses()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(VehicleClass::class)->findAll();

        return $this->extractType2EnumValues($items, self::ENUM_TYPE_DVLA);
    }

    public function getCountriesOfRegistration()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(CountryOfRegistration::class)->findAll();

        return $this->extractType2EnumValues($items, self::ENUM_TYPE_STANDARD);
    }

    public function getTransmissionTypes()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(TransmissionType::class)->findAll();

        return $this->extractType2EnumValues($items, self::ENUM_TYPE_DVLA);
    }

    public function getMotTestTypes()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(MotTestType::class)->findBy([], ['position' => 'ASC']);

        return $this->extractItemsWithPosition($items);
    }

    public function getPersonSystemRoles()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(PersonSystemRole::class)->findAll();
        $values = [];
        foreach ($items as $item) {
            $values[$item->getId()] = [
                'id' => $item->getId(),
                'code' => $item->getName(),
                'name' => $item->getFullName(),
            ];
        }

        return $values;
    }

    /**
     * Generates an array of business roles for use in the catalog.
     *
     * @return array
     */
    public function getBusinessRoles()
    {
        $values = [];
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $organisationRoles = $this->entityManager->getRepository(OrganisationBusinessRole::class)->findAll();
        $siteRoles = $this->entityManager->getRepository(SiteBusinessRole::class)->findAll();

        foreach ($organisationRoles as $organisationRole) {
            $values[] = [
                'role' => 'organisation',
                'id' => $organisationRole->getId(),
                'code' => $organisationRole->getShortName(),
                'name' => $organisationRole->getName(),
            ];
        }

        foreach ($siteRoles as $siteRole) {
            $values[] = [
                'role' => 'site',
                'id' => $siteRole->getId(),
                'code' => $siteRole->getCode(),
                'name' => $siteRole->getName(),
            ];
        }

        return $values;
    }

    public function getOrganisationBusinessRoles()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(OrganisationBusinessRole::class)->findAll();

        /* TODO: refactor to use existing extractType2EnumValues method
         * after VM-8254's change to organisation_business_role column names have taken place
         * the rest of this method can be replaced with the following line:
         * return $this->extractType2EnumValues($items, self::ENUM_TYPE_STANDARD);
         */
        $values = [];
        foreach ($items as $item) {
            $extracted = $this->objectHydrator->extract($item);

            $values[] = [
                'id' => $extracted['id'],
                'code' => $extracted['name'],
                'name' => $extracted['fullName'],
            ];
        }

        return $values;
    }

    public function getSiteBusinessRoles()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(SiteBusinessRole::class)->findAll();

        return $this->extractType2EnumValues($items, self::ENUM_TYPE_STANDARD);
    }

    public function getBrakeTestType()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);

        $items = $this->entityManager->getRepository(BrakeTestType::class)->findAll();

        return $this->extractType2EnumValues($items, self::ENUM_TYPE_STANDARD);
    }

    public function getEquipmentModelStatus()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(EquipmentModelStatus::class)->findAll();

        return $this->extractType2EnumValues($items, self::ENUM_TYPE_STANDARD);
    }

    public function getReasonsForEmptyVRM()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(EmptyVrmReason::class)->findAll();

        return $this->extractType2EnumValues($items, self::ENUM_TYPE_STANDARD);
    }

    public function getReasonsForEmptyVIN()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(EmptyVinReason::class)->findAll();

        return $this->extractType2EnumValues($items, self::ENUM_TYPE_STANDARD);
    }

    public function getQualificationStatus()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(AuthorisationForTestingMotStatus::class)->findAll();

        return $this->extractType2EnumValues($items, self::ENUM_TYPE_STANDARD);
    }

    public function getEventTypesWithOutcomes()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $repo = $this->entityManager->getRepository(EventTypeOutcomeCategoryMap::class);
        $eventTypeOutcomes = $repo->getEventTypeWithOutcomes();

        // Mapping the flat result into the structure we desire
        $i = 0;
        while ($i < sizeof($eventTypeOutcomes)) {
            $eventTypeOutcome = $eventTypeOutcomes[$i];

            $typeCode = $eventTypeOutcomes[$i]['typeCode'];
            $outcomes = [];
            while ($eventTypeOutcomes[$i]['typeCode'] == $typeCode && $i < count($eventTypeOutcomes)) {
                $outcomes[] = [
                    'code' => $eventTypeOutcomes[$i]['outcomeCode'],
                    'name' => $eventTypeOutcomes[$i]['outcomeName'],
                ];
                ++$i;
            }

            $eventType = [
                'code' => $eventTypeOutcome['typeCode'],
                'name' => $eventTypeOutcome['typeName'],
                'outcomes' => $outcomes,
            ];
            $return[$eventTypeOutcome['categoryCode']][] = $eventType;
        }

        return $return;
    }

    private function extractType2EnumValues($items, $type = self::ENUM_TYPE_STANDARD)
    {
        $values = [];
        foreach ($items as $item) {
            $extracted = $this->objectHydrator->extract($item);

            $values[] = $type === self::ENUM_TYPE_DVLA ?
                $this->mapDvlaType2Enum($extracted) : $this->mapStandardType2Enum($extracted);
        }

        return $values;
    }

    private function mapStandardType2Enum($data)
    {
        return [
            'id' => $data['id'],
            'code' => $data['code'],
            'name' => $data['name'],
        ];
    }

    private function mapDvlaType2Enum($data)
    {
        return [
            'id' => $data['id'],
            'name' => $data['name'],
        ];
    }

    /**
     * extracts the passed items into php arrays
     * - filters the position field.
     *
     * @param $items
     * @param $filters
     *
     * @return array
     */
    private function extractItems($items, $filters = [])
    {
        return $this->extractItemsInternal($items, $filters, false);
    }

    private function extractItemsWithPosition($items, $filters = [])
    {
        return $this->extractItemsInternal($items, $filters, true);
    }

    private function extractItemsInternal($items, $filters = [], $includePosition = false)
    {
        $values = [];
        if ($items) {
            foreach ($items as $item) {
                $extracted = $this->objectHydrator->extract($item);
                if (isset($extracted['position']) && !$includePosition) {
                    unset($extracted['position']);
                }
                if (is_array($filters) && count($filters) > 0) {
                    foreach ($filters as $filter) {
                        if (isset($extracted[$filter])) {
                            unset($extracted[$filter]);
                        }
                    }
                }

                $values[] = $extracted;
            }
        }

        return $values;
    }

    /**
     * @param $items
     * @param $className
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    private function checkAndExtractItems($items, $className)
    {
        if (!$items) {
            throw new NotFoundException($className, null);
        }

        return $this->extractItems($items);
    }

    /**
     * @return array
     */
    public function getSiteStatuses()
    {
        /** @var SiteStatusRepository $repo */
        $repo = $this->entityManager->getRepository(SiteStatus::class);
        $items = $repo->getAll();

        return $this->extractType2EnumValues($items);
    }

    /**
     * Fetches all availible countries.
     *
     * @return array
     */
    public function getCountries()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(Country::class)->findAll();

        return $this->extractType2EnumValues($items, self::ENUM_TYPE_STANDARD);
    }

    /**
     * @return array
     */
    public function getSiteTypes()
    {
        /** @var SiteStatusRepository $repo */
        $repo = $this->entityManager->getRepository(SiteType::class);
        $items = $repo->findAll();

        return $this->extractType2EnumValues($items, self::ENUM_TYPE_STANDARD);
    }

    /**
     * @return array
     */
    public function getOrganisationCompanyTypes()
    {
        /** @var SiteStatusRepository $repo */
        $repo = $this->entityManager->getRepository(CompanyType::class);
        $items = $repo->findAll();

        return $this->extractType2EnumValues($items, self::ENUM_TYPE_STANDARD);
    }

    /**
     * @return array
     */
    public function getAuthForAuthorisedExaminerStatuses()
    {
        $this->authService->assertGranted(PermissionInSystem::DATA_CATALOG_READ);
        $items = $this->entityManager->getRepository(AuthForAeStatus::class)->findAll();

        return $this->extractType2EnumValues($items, self::ENUM_TYPE_STANDARD);
    }
}
