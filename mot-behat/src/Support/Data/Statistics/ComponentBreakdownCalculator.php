<?php

namespace Dvsa\Mot\Behat\Support\Data\Statistics;

use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use Dvsa\Mot\Behat\Support\Data\Statistics\Model\ComponentBreakdown\GroupACategoriesTree;
use Dvsa\Mot\Behat\Support\Data\Statistics\Model\ComponentBreakdown\GroupBCategoriesTree;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\MotTestingPerformanceDto;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\VehicleClassGroupCode;

class ComponentBreakdownCalculator
{
    private $motCollection;
    private $filter;

    public function __construct(DataCollection $motCollection)
    {
        if ($motCollection->getExpectedInstance() !== MotTestDto::class) {
            throw new \InvalidArgumentException(sprintf("Expected collection of '%s', got '%s", MotTestDto::class, $motCollection->getExpectedInstance()));
        }

        $this->motCollection = $motCollection;
        $this->filter = new MotTestFilter();
    }

    /**
     * @param int $siteId
     * @param int $testerId
     * @param int $months
     * @param string $group
     * @return ComponentBreakdownDto
     */
    public function calculateStatisticsForSite($siteId, $testerId, $months, $group)
    {
        if (VehicleClassGroupCode::BIKES === $group) {
            return $this->calculateStatisticsForGroupAAtSite($siteId, $testerId, $months);
        } elseif (VehicleClassGroupCode::CARS_ETC === $group) {
            return $this->calculateStatisticsForGroupBAtSite($siteId, $testerId, $months);
        } else {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @param int $siteId
     * @param int $testerId
     * @param int $months
     * @return ComponentBreakdownDto
     */
    public function calculateStatisticsForGroupAAtSite($siteId, $testerId, $months)
    {
        $motTests = $this->filterOutInvalidTests($this->motCollection, $testerId, $months);
        $motTests = $this->filter->filterBySiteId($motTests, $siteId);

        $groupAmotTests = $this->filter->filterTestsForGroupA($motTests);
        $testsGroupedInCategories = $this->groupByCategoriesAndVehicleGroupA($groupAmotTests);

        $components = $this->calculateComponentsStats($testsGroupedInCategories, $this->countFailedTests($groupAmotTests));
        $groupPerformance = $this->calculateGroupPerformance($groupAmotTests);

        $dto = new ComponentBreakdownDto();
        $dto
            ->setComponents($components)
            ->setGroupPerformance($groupPerformance);

        return $dto;
    }

    public function calculateStatisticsForGroupBAtSite($siteId, $testerId, $months)
    {
        $motTests = $this->filterOutInvalidTests($this->motCollection, $testerId, $months);
        $motTests = $this->filter->filterBySiteId($motTests, $siteId);

        $groupBmotTests = $this->filter->filterTestsForGroupB($motTests);
        $testsGroupedInCategories = $this->groupByCategoriesAndVehicleGroupB($groupBmotTests);

        $components = $this->calculateComponentsStats($testsGroupedInCategories, $this->countFailedTests($groupBmotTests));
        $groupPerformance = $this->calculateGroupPerformance($groupBmotTests);

        $dto = new ComponentBreakdownDto();
        $dto
            ->setComponents($components)
            ->setGroupPerformance($groupPerformance);

        return $dto;
    }

    /**
     * @param int $testerId
     * @param int $months
     * @param string $group
     * @return ComponentBreakdownDto
     */
    public function calculateStatisticsForAllSites($testerId, $months, $group)
    {
        if (VehicleClassGroupCode::BIKES === $group) {
            return $this->calculateStatisticsForGroupAAtAllSites($testerId, $months);
        } elseif (VehicleClassGroupCode::CARS_ETC === $group) {
            return $this->calculateStatisticsForGroupBAtAllSites($testerId, $months);
        } else {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @param int $testerId
     * @param int $months
     * @return ComponentBreakdownDto
     */
    public function calculateStatisticsForGroupAAtAllSites($testerId, $months)
    {
        $motTests = $this->filterOutInvalidTests($this->motCollection, $testerId, $months);

        $groupAmotTests = $this->filter->filterTestsForGroupA($motTests);
        $testsGroupedInCategories = $this->groupByCategoriesAndVehicleGroupA($groupAmotTests);

        $components = $this->calculateComponentsStats($testsGroupedInCategories, $this->countFailedTests($groupAmotTests));
        $groupPerformance = $this->calculateGroupPerformance($groupAmotTests);

        $dto = new ComponentBreakdownDto();
        $dto
            ->setComponents($components)
            ->setGroupPerformance($groupPerformance);

        return $dto;
    }

    /**
     * @param int $testerId
     * @param int $months
     * @return ComponentBreakdownDto
     */
    public function calculateStatisticsForGroupBAtAllSites($testerId, $months)
    {
        $motTests = $this->filterOutInvalidTests($this->motCollection, $testerId, $months);

        $groupBmotTests = $this->filter->filterTestsForGroupB($motTests);
        $testsGroupedInCategories = $this->groupByCategoriesAndVehicleGroupB($groupBmotTests);

        $components = $this->calculateComponentsStats($testsGroupedInCategories, $this->countFailedTests($groupBmotTests));
        $groupPerformance = $this->calculateGroupPerformance($groupBmotTests);

        $dto = new ComponentBreakdownDto();
        $dto
            ->setComponents($components)
            ->setGroupPerformance($groupPerformance);

        return $dto;
    }

    private function filterOutInvalidTests(DataCollection $motCollection, $testerId, $months)
    {
        $motTests = $this->filter->filterByMonths($motCollection, $months);
        $motTests = $this->filter->filterByTesterId($motTests, $testerId);

        return $motTests;
    }

    private function groupByCategoriesAndVehicleGroupA(DataCollection $motCollection)
    {
        return $this->groupByCategories($motCollection, GroupACategoriesTree::get());
    }

    private function groupByCategoriesAndVehicleGroupB(DataCollection $motCollection)
    {
        return $this->groupByCategories($motCollection, GroupBCategoriesTree::get());
    }

    private function groupByCategories(DataCollection $motCollection, array $categories)
    {
        /** @var DataCollection[] $tests */
        $tests = [];

        foreach ($categories as $categoryId => $categoryRfrs) {
            if (array_key_exists($categoryId, $tests)) {
                $motCategoryCollection = $tests[$categoryId];
            } else {
                $motCategoryCollection = new DataCollection(MotTestDto::class);
                $tests[$categoryId] = $motCategoryCollection;
            }

            /** @var MotTestDto $mot */
            foreach ($motCollection as $mot) {
                $rfrs = $mot->getReasonsForRejection();
                if (array_key_exists("FAIL", $rfrs) === false) {
                    continue;
                }

                $failedRfrs = $rfrs["FAIL"];

                foreach ($failedRfrs as $rfr) {
                    if (in_array($rfr["rfrId"], $categoryRfrs)) {
                        $motCategoryCollection->add($mot, $mot->getMotTestNumber());
                        $tests[$categoryId] = $motCategoryCollection;
                    }
                }
            }
        }

        return $tests;
    }

    /**
     * @param DataCollection $motCollection
     * @return MotTestingPerformanceDto
     */
    private function calculateGroupPerformance(DataCollection $motCollection)
    {
        $stats = new PerformanceCalculator($motCollection);

        $motTestingPerformance = new MotTestingPerformanceDto();
        $motTestingPerformance
            ->setTotal($stats->getTotal())
            ->setAverageTime($stats->getAverageTime())
            ->setPercentageFailed($stats->getPercentageFailed())
            ->setAverageVehicleAgeInMonths($stats->getAverageVehicleAgeInMonths())
            ->setIsAverageVehicleAgeAvailable($stats->getIsAverageVehicleAgeAvailable());

        return $motTestingPerformance;
    }

    private function countFailedTests(DataCollection $motCollection)
    {
        $numOfFailedTests = 0;
        foreach ($motCollection as $mot) {
            if ($mot->getStatus() === MotTestStatusName::FAILED) {
                $numOfFailedTests++;
            }

        }

        return $numOfFailedTests;
    }

    /**
     * @param DataCollection[] $testsGroupedInCategories
     * @param int $numbOfFailedTests
     * @return ComponentDto[]
     */
    private function calculateComponentsStats(array $testsGroupedInCategories, $numbOfFailedTests)
    {
        $components = [];

        /**
         * @var int $categoryId
         * @var DataCollection $motCollection
         */
        foreach ($testsGroupedInCategories as $categoryId => $motCollection) {
            $percentageFailed = ($this->countFailedTests($motCollection) / $numbOfFailedTests) * 100;

            $component = new ComponentDto();
            $component
                ->setId($categoryId)
                ->setPercentageFailed($percentageFailed);

            $components[] = $component;
        }

        return $components;
    }
}
