<?php

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Constants\MotConfig\ElasticsearchConfigKeys;
use DvsaCommon\Constants\MotConfig\MotConfigKeys;
use DvsaCommon\Constants\ReasonForRejection as ReasonForRejectionConstants;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionInterface;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\RfrDeficiencyCategory;
use DvsaEntities\Entity\TestItemSelector;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Repository\RfrRepository;
use DvsaEntities\Repository\TestItemCategoryRepository;
use DvsaMotApi\Formatting\DefectSentenceCaseConverter;
use DvsaMotApi\Service\ReasonForRejection\ReasonForRejectionTypeConverterService;

/**
 * Class TestItemSelectorService.
 */
class TestItemSelectorService
{
    const ROOT_SELECTOR_ID = 0;
    const RECURSION_MAX_LEVEL = 100;

    /** @var DoctrineHydrator */
    protected $objectHydrator;

    /** @var AuthorisationServiceInterface */
    protected $authService;

    protected $motTestMapper;

    /** @var RfrRepository */
    private $rfrRepository;

    /** @var TestItemCategoryRepository */
    private $testItemCategoryRepository;

    /** @var array */
    private $disabledRfrs = [];

    /** @var DefectSentenceCaseConverter */
    private $defectSentenceCaseConverter;

    /** @var MotConfig */
    private $motConfig;

    /** @var EntityManager */
    private $entityManager;

    public function __construct(
        EntityManager $entityManager,
        DoctrineHydrator $objectHydrator,
        RfrRepository $rfrRepository,
        AuthorisationServiceInterface $authService,
        TestItemCategoryRepository $testItemCategoryRepository,
        array $disabledRfrs,
        DefectSentenceCaseConverter $defectSentenceCaseConverter,
        MotConfig $motConfig
    ) {
        $this->entityManager = $entityManager;
        $this->objectHydrator = $objectHydrator;
        $this->rfrRepository = $rfrRepository;
        $this->authService = $authService;
        $this->testItemCategoryRepository = $testItemCategoryRepository;
        $this->disabledRfrs = $disabledRfrs;
        $this->defectSentenceCaseConverter = $defectSentenceCaseConverter;
        $this->motConfig = $motConfig;
    }

    /**
     * @param $vehicleClass
     *
     * @return array
     */
    public function getTestItemSelectorsDataByClass($vehicleClass)
    {
        $this->authService->assertGranted(PermissionInSystem::RFR_LIST);

        $testItemSelectorResult = $this->getTestItemSelectorById(self::ROOT_SELECTOR_ID, $vehicleClass);
        $testItemSelector = current($testItemSelectorResult);

        $testItemSelectors = $this->testItemCategoryRepository->findByVehicleClass($vehicleClass);

        // assuming top level test item selector has no RFRs
        return $this->getOutputData($testItemSelector, $testItemSelectors);
    }

    /**
     * @param MotTest $motTest
     *
     * @return array Array of items in the following format:
     *               [
     *               "name": "Parking brake",
     *               "items": [
     *               "Electronic parking brake",
     *               "Fitment",
     *               "Condition"
     *               ]
     *               ]
     */
    public function getCurrentNonEmptyTestItemCategoryNamesByMotTest($motTest)
    {
        $this->authService->assertGranted(PermissionInSystem::RFR_LIST);

        $vehicleClassCode = $motTest->getVehicleClass()->getCode();
        $data = $this->rfrRepository->getCurrentTestItemCategoriesWithRfrsByVehicleCriteria($vehicleClassCode);

        $array = [];
        if (is_array($data)) {
            foreach ($data as $item) {
                $array[$item['parentName']] [] = $item['name'];
            }
        }

        $json = [];
        foreach ($array as $key => $value) {
            $json [] = [
                'name' => $key,
                'items' => $value,
            ];
        }

        return $json;
    }

    protected function getTestItemSelectorById($id, $vehicleClass)
    {
        $testItemSelector = $this->testItemCategoryRepository->findByIdAndVehicleClass($id, $vehicleClass);

        if (empty($testItemSelector) || !$this->isCurrentRfrApplicableToRole($testItemSelector[0])) {
            throw new NotFoundException('Test Item Selector', $id);
        }

        return $testItemSelector;
    }

    protected function getOutputData(
        $testItemSelector,
        $testItemSelectors,
        $parentTestItemSelectors = [],
        $reasonsForRejection = []
    ) {
        return [
            'testItemSelector' => $this->extractTestItem($testItemSelector),
            'parentTestItemSelectors' => $this->extractTestItemSelectors($parentTestItemSelectors),
            'testItemSelectors' => $this->extractTestItemSelectors($testItemSelectors),
            'reasonsForRejection' => $this->extractReasonsForRejection($reasonsForRejection),
        ];
    }

    /**
     * @param TestItemSelector $defectCategory
     *
     * @return array|null
     */
    protected function extractTestItem(TestItemSelector $defectCategory)
    {
        if (!$this->isCurrentRfrApplicableToRole($defectCategory)) {
            return;
        }
        $hydratedCategoryDetails = $this->objectHydrator->extract($defectCategory);
        $categoryDetails = $this->defectSentenceCaseConverter->getDetailsForDefectCategories($defectCategory);
        if (!empty($categoryDetails['name'])) {
            $hydratedCategoryDetails['name'] = $categoryDetails['name'];
        }

        return $hydratedCategoryDetails;
    }

    protected function extractTestItemSelectors($testItemSelectors)
    {
        $testItemSelectorData = [];
        if ($testItemSelectors) {
            foreach ($testItemSelectors as $testItem) {
                $extractedTestItem = $this->extractTestItem($testItem);
                isset($extractedTestItem) ? $testItemSelectorData[] = $extractedTestItem : '';
            }
        }

        return $testItemSelectorData;
    }

    /**
     * @param ReasonForRejection[] $reasonsForRejection
     *
     * @return array
     */
    protected function extractReasonsForRejection($reasonsForRejection)
    {
        $reasonsForRejectionData = [];
        if ($reasonsForRejection) {
            foreach ($reasonsForRejection as $reasonForRejection) {
                if ($this->shouldHideRfr($reasonForRejection->getRfrId())) {
                    continue;
                }

                $reasonsForRejectionData[] = $this->extractReasonForRejection($reasonForRejection);
            }
        }

        return $reasonsForRejectionData;
    }

    private function extractVehicleClassessIntoRfr(array $testItemRfrData)
    {
        $extractedVehicleClasses = [];

        if (key_exists('vehicleClasses', $testItemRfrData) &&
            $testItemRfrData['vehicleClasses'] instanceof PersistentCollection) {
            $extractedVehicleClasses = array_map(function (VehicleClass $class) {
                return $class->getCode();
            }, $testItemRfrData['vehicleClasses']->toArray());
        }

        return $extractedVehicleClasses;
    }

    /**
     * @param ReasonForRejection $testItemRfr
     *
     * @return array
     */
    protected function extractReasonForRejection(ReasonForRejection $testItemRfr)
    {
        $testItemRfrData = $this->objectHydrator->extract($testItemRfr);

        unset($testItemRfrData['descriptions']);
        $defectDetails = $this->defectSentenceCaseConverter->getDefectDetailsForListAndSearch($testItemRfr);
        if (!empty($defectDetails)) {
            $testItemRfrData = array_merge($testItemRfrData, $defectDetails);
        }

        /** @var RfrDeficiencyCategory $testItemRfrDeficiencyCategory */
        $testItemRfrDeficiencyCategory = $testItemRfrData['rfrDeficiencyCategory'];
        if ($testItemRfrDeficiencyCategory !== null) {
            $testItemRfrData['deficiencyCategoryCode'] = $testItemRfrDeficiencyCategory->getCode();
        }

        unset($testItemRfrData['rfrDeficiencyCategory']);

        $testItemRfrData['vehicleClasses'] = $this->extractVehicleClassessIntoRfr($testItemRfrData);

        return $testItemRfrData;
    }

    public function getTestItemSelectorsData($id, $vehicleClass)
    {
        $role = $this->determineRole();
        $this->authService->assertGranted(PermissionInSystem::RFR_LIST);

        $itemsCollection = [];

        do {
            $data = $this->getTestItemsByParentId($id, $vehicleClass, $role);
            $itemsCollection[] = $data;
            $id = $data['testItemSelector']['parentTestItemSelectorId'];
        } while ($data['testItemSelector']['id'] !== 0);

        return $itemsCollection;
    }

    private function getTestItemsByParentId($id, $vehicleClass, $role)
    {
        $testItemSelectorResult = $this->getTestItemSelectorById($id, $vehicleClass);
        $testItemSelector = current($testItemSelectorResult);

        $testItemSelectors = $this->testItemCategoryRepository->findByParentIdAndVehicleClass($id, $vehicleClass, $role);

        foreach ($testItemSelectors as $key => $value) {
            if ($this->isOldSelector($value)) {
                unset($testItemSelectors[$key]);
            }
        }

        $reasonsForRejection = $this->rfrRepository->findByIdAndVehicleClassForUserRole($id, $vehicleClass, $role);

        // TODO verify other RFR rules

        $parentItemSelectors = $this->getParentsOfTestItemSelector($testItemSelector);

        return $this->getOutputData(
            $testItemSelector,
            $testItemSelectors,
            $parentItemSelectors,
            $reasonsForRejection
        );
    }

    protected function getParentsOfTestItemSelector(TestItemSelector $testItemSelector)
    {
        $parents = [];
        $currentTestItemSelector = $testItemSelector;
        $iterations = 0;
        while ($currentTestItemSelector->getId() !== $currentTestItemSelector->getParentTestItemSelectorId()) {
            $currentTestItemSelector = $this->entityManager->find(
                TestItemSelector::class,
                $currentTestItemSelector->getParentTestItemSelectorId()
            );
            $parents[] = $currentTestItemSelector;
            ++$iterations;
            if ($iterations > self::RECURSION_MAX_LEVEL) {
                throw new \LogicException('Recursion level exceeded: '.self::RECURSION_MAX_LEVEL);
            }
        }

        return $parents;
    }

    public function getAllReasonsForRejection(): array
    {
        $this->authService->assertGranted(PermissionInSystem::RFR_LIST);

        return $reasonsForRejection = $this->rfrRepository->findAll();
    }

    /**
     * @param array $reasonsForRejection
     *
     * @return array
     */
    public function formatReasonsForRejectionForElasticSearch(array $reasonsForRejection)
    {
        $reasonForRejectionTypeConverterService = new ReasonForRejectionTypeConverterService();
        $elasticSearchRFRs = [];

        foreach ($reasonsForRejection as $rfr) {
            if ($this->shouldHideRfr($rfr['rfrId'])) {
                continue;
            }

            $rfr = $reasonForRejectionTypeConverterService->convert($rfr);

            $elasticsearchConfig = $this->motConfig->get(MotConfigKeys::ELASTICSEARCH);

            $elasticSearchRFRs[] = [
                ['index' => [
                         '_index' => $elasticsearchConfig[ElasticsearchConfigKeys::ES_INDEX_NAME],
                         '_type' => 'reasons_for_rejection',
                         '_id' => $rfr['rfrId'],
                     ],
                ],
                $rfr,
            ];
        }

        return $elasticSearchRFRs;
    }

    protected function extractTestItemSelector($testItemSelectors)
    {
        $returnTestItemSelector = null;

        if ($testItemSelectors) {
            $returnTestItemSelector = $this->extractTestItem(current($testItemSelectors));
        }

        return $returnTestItemSelector;
    }

    protected function determineRole()
    {
        $role = SearchReasonForRejectionInterface::TESTER_ROLE_FLAG;
        if ($this->authService->isGranted(PermissionInSystem::VE_RFR_ITEMS_NOT_TESTED)) {
            $role = SearchReasonForRejectionInterface::VEHICLE_EXAMINER_ROLE_FLAG;
        }

        return $role;
    }

    protected function isCurrentRfrApplicableToRole(TestItemSelector $testItem)
    {
        $applicable = true;
        $testerSpecificRfrs = [
            ReasonForRejectionConstants::CLASS_12_BRAKE_PERFORMANCE_NOT_TESTED_RFR_ID,
            ReasonForRejectionConstants::CLASS_12_HEADLAMP_AIM_NOT_TESTED_RFR_ID,
            ReasonForRejectionConstants::CLASS_3457_BRAKE_PERFORMANCE_NOT_TESTED_POST_EU_RFR_ID,
            ReasonForRejectionConstants::CLASS_3457_BRAKE_PERFORMANCE_NOT_TESTED_RFR_ID,
            ReasonForRejectionConstants::CLASS_3457_EMISSIONS_NOT_TESTED_RFR_ID,
            ReasonForRejectionConstants::CLASS_3457_HEADLAMP_AIM_NOT_TESTED_RFR_ID,
        ];

        //TODO: needs to use PermissionInSystem::TESTER_RFR_ITEMS_NOT_TESTED but needs to wait on fix. VM-1340
        if ((!$this->authService->isGranted(PermissionInSystem::VE_RFR_ITEMS_NOT_TESTED)
                && $testItem->getSectionTestItemSelectorId() === ReasonForRejectionConstants::ITEM_NOT_TESTED_SELECTOR_ID)
            || ($this->authService->isGranted(PermissionInSystem::VE_RFR_ITEMS_NOT_TESTED)
                && in_array(
                    $testItem->getId(),
                    $testerSpecificRfrs
                ))
        ) {
            $applicable = false;
        }

        return $applicable;
    }

    public function shouldHideRfr($rfrId)
    {
        return in_array($rfrId, $this->disabledRfrs);
    }

    /**
     * @param TestItemSelector $selector
     *
     * @return bool
     */
    private function isOldSelector(TestItemSelector $selector)
    {
        return strpos($selector->getDescriptions()->getValues()[0]->getName(), '(old)') !== false;
    }
}
