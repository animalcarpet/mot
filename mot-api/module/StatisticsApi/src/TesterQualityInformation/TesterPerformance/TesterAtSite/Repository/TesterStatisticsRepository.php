<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Repository\AbstractStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Site\Repository\SiteManyGroupsStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\Repository\TesterManyGroupsStatisticsRepository;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TesterStatisticsRepository extends AbstractStatisticsRepository implements AutoWireableInterface
{
    public function getForSite($siteId, $year, $month)
    {
        return (new SiteManyGroupsStatisticsRepository($this->entityManager))->get($siteId, $year, $month);
    }

    public function getForTester($testerId, $year, $month)
    {
        return (new TesterManyGroupsStatisticsRepository($this->entityManager))->get($testerId, $year, $month);
    }
}
