<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Site\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryResult\ComponentFailRateResult;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\SiteComponentStatisticsDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class SiteAverageStatisticsCalculatorService implements AutoWireableInterface
{
    /**
     * @param $siteId
     * @param $group
     * @param $monthRange
     * @param ComponentFailRateResult[] $componentStatistics
     * @param $statisticsTotalCount
     * @return SiteComponentStatisticsDto
     */
    public function calculate(int $siteId, string $group, int $monthRange, array $componentStatistics, int $statisticsTotalCount) {
        $dto = new SiteComponentStatisticsDto();

        $dto->setSiteId($siteId)
            ->setGroup($group)
            ->setMonthRange($monthRange)
            ->setComponents($this->calculateStats($componentStatistics, $statisticsTotalCount));

        return $dto;
    }

    /**
     * @param ComponentFailRateResult[] $rawComponents
     * @param int $total
     * @return ComponentDto[]
     */
    private function calculateStats(array $rawComponents, int $total)
    {
        $components = [];
        /** @var ComponentFailRateResult $rawComponent */
        foreach ($rawComponents as $rawComponent) {
            $component = new ComponentDto();
            $component->setId($rawComponent->getTestItemCategoryId());
            $component->setName($rawComponent->getTestItemCategoryName());
            if ($total === 0) {
                $percentageFailed = 0;
            } else {
                $percentageFailed = (100 * $rawComponent->getFailedCount()) / $total;
            }
            $component->setPercentageFailed($percentageFailed);

            $components[] = $component;
        }

        return $components;
    }
}
