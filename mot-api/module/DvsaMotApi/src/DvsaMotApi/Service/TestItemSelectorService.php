<?php

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Constants\ReasonForRejection as ReasonForRejectionConstants;
use DvsaCommon\Enum\LanguageTypeCode;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\TestItemSelector;
use DvsaEntities\Repository\RfrRepository;
use DvsaEntities\Repository\TestItemCategoryRepository;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Formatting\DefectSentenceCaseConverter;

/**
 * Class TestItemSelectorService.
 */
class TestItemSelectorService extends AbstractService
{
    const ROOT_SELECTOR_ID = 0;
    const SEARCH_MAX_COUNT = 10;
    const RECURSION_MAX_LEVEL = 100;

    const VE_ROLE_FLAG = 'v';
    const TESTER_ROLE_FLAG = 't';

    protected $objectHydrator;
    protected $authService;
    protected $motTestMapper;

    /** @var RfrRepository */
    private $rfrRepository;
    private $testItemCategoryRepository;
    private $disabledRfrs = [];

    /**
     * @var DefectSentenceCaseConverter
     */
    private $defectSentenceCaseConverter;

    /** @var FeatureToggles */
    private $featureToggles;

    public function __construct(
        EntityManager $entityManager,
        DoctrineHydrator $objectHydrator,
        RfrRepository $rfrRepository,
        AuthorisationServiceInterface $authService,
        TestItemCategoryRepository $testItemCategoryRepository,
        array $disabledRfrs,
        FeatureToggles $featureToggles,
        DefectSentenceCaseConverter $defectSentenceCaseConverter
    ) {
        parent::__construct($entityManager);

        $this->objectHydrator = $objectHydrator;
        $this->rfrRepository = $rfrRepository;
        $this->authService = $authService;
        $this->testItemCategoryRepository = $testItemCategoryRepository;
        $this->disabledRfrs = $disabledRfrs;
        $this->featureToggles = $featureToggles;
        $this->defectSentenceCaseConverter = $defectSentenceCaseConverter;
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

        return $testItemRfrData;
    }

    public function getTestItemSelectorsData($id, $vehicleClass)
    {
        $role = $this->determineRole();
        $this->authService->assertGranted(PermissionInSystem::RFR_LIST);

        $testItemSelectorResult = $this->getTestItemSelectorById($id, $vehicleClass);
        $testItemSelector = current($testItemSelectorResult);

        $testItemSelectors = $this->testItemCategoryRepository->findByParentIdAndVehicleClass($id, $vehicleClass);

        if (true === $this->featureToggles->isEnabled(FeatureToggle::TEST_RESULT_ENTRY_IMPROVEMENTS)) {
            foreach ($testItemSelectors as $key => $value) {
                if ($this->isOldSelector($value) ||
                    $this->hasNoTestItemSelectorsOrReasonsForRejection($value, $vehicleClass, $role)) {
                    unset($testItemSelectors[$key]);
                }
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

    /**
     * @param $vehicleClass
     * @param string $searchString
     * @param int    $start
     * @param int    $end
     *
     * @return array
     */
    public function searchReasonsForRejection($vehicleClass, $searchString, $start, $end)
    {
        if (true !== $this->featureToggles->isEnabled(FeatureToggle::TEST_RESULT_ENTRY_IMPROVEMENTS)) {
            $role = $this->determineRole();

            $this->authService->assertGranted(PermissionInSystem::RFR_LIST);

            if ($start <= 0 || $end <= 0 || $end <= $start) {
                $start = 0;
                $end = self::SEARCH_MAX_COUNT + 1;
            } else {
                if (($end - $start) > self::SEARCH_MAX_COUNT) {
                    $end = $start + self::SEARCH_MAX_COUNT + 1;
                }
            }

            $reasonsForRejection = $this->rfrRepository->findBySearchQuery(
                $searchString, $vehicleClass, $role, $start, $end
            );

            $hasMore = false;
            if (count($reasonsForRejection) > self::SEARCH_MAX_COUNT) {
                $hasMore = true;
                $reasonsForRejection = array_slice($reasonsForRejection, 0, self::SEARCH_MAX_COUNT);
            }

            return [
                'searchDetails' => [
                    'count' => count($reasonsForRejection),
                    'hasMore' => $hasMore,
                ],
                'reasonsForRejection' => $this->extractReasonsForRejection($reasonsForRejection),
            ];
        } else {
            /*
             * I had to do this as the code above doesn't actually function
             * as you'd expect. $end does nothing and the count returned
             * is broken, it only ever returns 10.
             *
             * So I'm just returning all the RFRs corresponding to the search
             * term and dealing with it in the front end. Searching for 'and'
             * returns >500 results and takes around half a second to respond
             * to the front end so it's not too bad.
             */
            $this->authService->assertGranted(PermissionInSystem::RFR_LIST);

            $role = $this->determineRole();
            $reasonsForRejection = $this->rfrRepository->findBySearchQuery($searchString, $vehicleClass, $role, 0, 9999);
            $rfrCount = count($reasonsForRejection);

            return [
                'searchDetails' => ['count' => $rfrCount],
                'reasonsForRejection' => $this->extractReasonsForRejection($reasonsForRejection),
            ];
        }
    }

    protected function extractTestItemSelector($testItemSelectors)
    {
        $returnTestItemSelector = null;

        if ($testItemSelectors) {
            $returnTestItemSelector = $this->extractTestItem(current($testItemSelectors));
        }

        return $returnTestItemSelector;
    }

    /**
     * @param int $rfrId
     *
     * @return null|ReasonForRejection
     *                                 TODO move to RfrRepository
     */
    public function getReasonForRejectionById($rfrId)
    {
        if ($this->shouldHideRfr($rfrId)) {
            return;
        }

        $reasonForRejection = $this->entityManager->getRepository(ReasonForRejection::class)
            ->findOneBy(['rfrId' => $rfrId]);

        return $reasonForRejection;
    }

    protected function determineRole()
    {
        $role = self::TESTER_ROLE_FLAG;
        if ($this->authService->isGranted(PermissionInSystem::VE_RFR_ITEMS_NOT_TESTED)) {
            $role = self::VE_ROLE_FLAG;
        }

        return $role;
    }

    protected function isCurrentRfrApplicableToRole(TestItemSelector $testItem)
    {
        $applicable = true;
        $testerSpecificRfrs = [
            ReasonForRejectionConstants::CLASS_12_BRAKE_PERFORMANCE_NOT_TESTED_RFR_ID,
            ReasonForRejectionConstants::CLASS_12_HEADLAMP_AIM_NOT_TESTED_RFR_ID,
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

    private function shouldHideRfr($rfrId)
    {
        return in_array($rfrId, $this->disabledRfrs);
    }

    /**
     * @param TestItemSelector $selector
     * @param $vehicleClass
     * @param $role
     *
     * @return bool
     */
    private function hasNoTestItemSelectorsOrReasonsForRejection(TestItemSelector $selector, $vehicleClass, $role)
    {
        return empty($this->testItemCategoryRepository->findByParentIdAndVehicleClass(
                $selector->getId(), $vehicleClass))
            &&
            empty($this->rfrRepository->findByIdAndVehicleClassForUserRole(
                $selector->getId(), $vehicleClass, $role))
        ;
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

    /**
     * @param string $prependString
     * @param string $description
     *
     * @return string
     */
    private function prepend($prependString, $description)
    {
        if (empty($prependString) || empty($description)) {
            return $description;
        }

        return $prependString.' '.$description;
    }

    /**
     * @param ReasonForRejection $rfr
     *
     * @return array
     */
    private function getCategoryDescriptionsFromTestItemSelector(ReasonForRejection $rfr)
    {
        $categoryDescriptionInEnglish = '';
        $categoryDescriptionInWelsh = '';
        foreach ($rfr->getTestItemSelector()->getDescriptions() as $description) {
            $languageCode = $description->getLanguage()->getCode();
            if ($languageCode === LanguageTypeCode::ENGLISH || $languageCode === null) {
                $categoryDescriptionInEnglish = $description->getDescription();
            } else {
                $categoryDescriptionInWelsh = $description->getDescription();
            }
        }

        return array($categoryDescriptionInEnglish, $categoryDescriptionInWelsh);
    }

    /**
     * @param string $categoryDescription
     * @param string $descriptionOrAdvisoryText
     *
     * @return string
     */
    private function buildDescriptionOrAdvisoryText($categoryDescription, $descriptionOrAdvisoryText)
    {
        $concatenatedDescription = $this->prepend($categoryDescription, $descriptionOrAdvisoryText);
        $formattedDescription = DefectFormattingHelper::formatWithFirstOccurrenceOfEachAcronymExpanded($concatenatedDescription);

        return $formattedDescription;
    }
}
