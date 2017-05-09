<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\Repository\SingleGroupStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\QueryBuilder\TesterSingleGroupStatisticsQueryBuilder;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterAtSitePerformanceResult;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TesterSingleGroupStatisticsRepository extends SingleGroupStatisticsRepository implements AutoWireableInterface
{
    const PARAM_TESTER_ID = 'testerId';

    public function get($testerId, $groupCode, $year, $month)
    {
        return $this->getByParams([
            self::PARAM_TESTER_ID => $testerId,
            self::PARAM_GROUP_CODE => $groupCode,
            self::PARAM_YEAR => $year,
            self::PARAM_MONTH => $month,
        ]);
    }

    protected function getSql()
    {
        return (new TesterSingleGroupStatisticsQueryBuilder())->getSql();
    }

    protected function createTesterPerformanceResult(array $row)
    {
        $dbResult = new TesterAtSitePerformanceResult();
        $dbResult
            ->setTotalTime((float) $row['totalTime'])
            ->setFailedCount((int) $row['failedCount'])
            ->setAverageVehicleAgeInMonths((float) $row['averageVehicleAgeInMonths'])
            ->setIsAverageVehicleAgeAvailable(!is_null($row['averageVehicleAgeInMonths']))
            ->setTotalCount((int) $row ['totalCount'])
            ->setSiteName($row['siteName']);

        return $dbResult;
    }

    protected function buildResultSetMapping()
    {
        $rsm = parent::buildResultSetMapping();

        return $rsm->addScalarResult('siteName', 'siteName');
    }
}
