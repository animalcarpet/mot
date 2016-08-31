<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\Repository\SingleGroupStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryBuilder\TesterAtSiteSingleGroupStatisticsQueryBuilder;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterAtSitePerformanceResult;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TesterAtSiteSingleGroupStatisticsRepository extends SingleGroupStatisticsRepository implements AutoWireableInterface
{
    const PARAM_SITE_ID = "vtsId";
    const PARAM_TESTER_ID = "testerId";

    public function get($siteId, $testerId, $groupCode, $year, $month)
    {
        return $this->getByParams([
            self::PARAM_SITE_ID    => $siteId,
            self::PARAM_TESTER_ID  => $testerId,
            self::PARAM_GROUP_CODE => $groupCode,
            self::PARAM_YEAR       => $year,
            self::PARAM_MONTH      => $month,
        ]);
    }

    protected function getSql()
    {
        return (new TesterAtSiteSingleGroupStatisticsQueryBuilder())->getSql();
    }

    protected function buildResultSetMapping()
    {
        $rsm = parent::buildResultSetMapping();

        return $rsm->addScalarResult('siteName', 'siteName');
    }

    protected function createTesterPerformanceResult(array $row)
    {
        $dbResult = new TesterAtSitePerformanceResult();
        $dbResult
            ->setTotalTime((double)$row['totalTime'])
            ->setFailedCount((int)$row['failedCount'])
            ->setAverageVehicleAgeInMonths((float)$row['averageVehicleAgeInMonths'])
            ->setIsAverageVehicleAgeAvailable(!is_null($row['averageVehicleAgeInMonths']))
            ->setTotalCount((int)$row ['totalCount'])
            ->setSiteName($row['siteName'])
        ;

        return $dbResult;
    }
}
