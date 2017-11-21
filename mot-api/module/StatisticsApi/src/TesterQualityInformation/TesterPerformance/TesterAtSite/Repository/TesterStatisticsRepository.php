<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Repository\AbstractStatisticsRepository;
use DvsaCommon\Date\LastMonthsDateRange;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Site\Repository\SiteManyGroupsStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\Repository\TesterManyGroupsStatisticsRepository;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TesterStatisticsRepository extends AbstractStatisticsRepository implements AutoWireableInterface
{
    public function getForSite($siteId, LastMonthsDateRange $monthRange)
    {
        return (new SiteManyGroupsStatisticsRepository($this->entityManager, $this->motConfig))->get($siteId, $monthRange);
    }

    public function getForTester($testerId, LastMonthsDateRange $monthRange)
    {
        return (new TesterManyGroupsStatisticsRepository($this->entityManager, $this->motConfig))->get($testerId, $monthRange);
    }
}
