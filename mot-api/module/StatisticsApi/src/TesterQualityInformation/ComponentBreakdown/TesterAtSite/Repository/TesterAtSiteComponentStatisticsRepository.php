<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\Repository\ComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\QueryBuilder\TesterAtSiteComponentBreakdownQueryBuilder;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TesterAtSiteComponentStatisticsRepository extends ComponentStatisticsRepository implements AutoWireableInterface
{
    const PARAM_SITE_ID = 'siteId';
    const PARAM_TESTER_ID = 'testerId';

    public function get($testerId, $siteId, $group, $monthRange)
    {
        $qb = new TesterAtSiteComponentBreakdownQueryBuilder();

        $this->setMonthsRangeConfiguration($monthRange);

        return $this->getResult($qb->getSql(), [
            self::PARAM_TESTER_ID => $testerId,
            self::PARAM_SITE_ID => $siteId,
            ComponentStatisticsRepository::PARAM_GROUP => $group,
            ComponentStatisticsRepository::PARAM_START_DATE => $this->startDate,
            ComponentStatisticsRepository::PARAM_END_DATE => $this->endDate,
        ]);
    }
}
