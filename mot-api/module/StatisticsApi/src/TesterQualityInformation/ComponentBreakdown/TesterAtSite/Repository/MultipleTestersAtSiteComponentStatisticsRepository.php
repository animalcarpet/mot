<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryResult\ComponentFailRateResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\Repository\ComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\QueryBuilder\MultipleTestersAtSiteComponentBreakdownQueryBuilder;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class MultipleTestersAtSiteComponentStatisticsRepository extends ComponentStatisticsRepository implements AutoWireableInterface
{
    const PARAM_SITE_ID = 'siteId';

    /**
     * @param int $siteId
     * @param string $group
     * @param LastMonthsDateRange $monthRange
     * @return ComponentFailRateResult[]
     */
    public function get(int $siteId, string $group, LastMonthsDateRange $monthRange): array
    {
        $qb  = new MultipleTestersAtSiteComponentBreakdownQueryBuilder();

        $this->setMonthsRangeConfiguration($monthRange);

        return $this->getResult($qb->getSql(), [
            self::PARAM_SITE_ID => $siteId,
            ComponentStatisticsRepository::PARAM_GROUP => $group,
            ComponentStatisticsRepository::PARAM_START_DATE => $this->startDate,
            ComponentStatisticsRepository::PARAM_END_DATE => $this->endDate
        ]);
    }
}
