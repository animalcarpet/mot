<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Repository\AbstractStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryBuilder\TesterAtSiteSingleGroupStatisticsQueryBuilder;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterAtSitePerformanceResult;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TesterAtSiteSingleGroupStatisticsRepository extends AbstractStatisticsRepository implements AutoWireableInterface
{
    const PARAM_SITE_ID = 'vtsId';
    const PARAM_TESTER_ID = 'testerId';
    const PARAM_GROUP_CODE = 'groupCode';

    public function get($siteId, $testerId, $groupCode, $monthRange)
    {
        $this->setMonthsRangeConfiguration($monthRange);
        $rsm = $this->buildResultSetMapping();

        $query = $this->getNativeQuery($this->getSql(), $rsm)
            ->setParameter(self::PARAM_SITE_ID, $siteId)
            ->setParameter(self::PARAM_TESTER_ID, $testerId)
            ->setParameter(self::PARAM_GROUP_CODE, $groupCode)
            ->setParameter('roleCode', SiteBusinessRoleCode::TESTER)
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate);

        $scalarResult = $query->getScalarResult();

        if (!empty($scalarResult))
        {
            $row = $scalarResult[0];
            return $this->createTesterPerformanceResult($row);
        }

        return null;
    }

    protected function getSql()
    {
        return (new TesterAtSiteSingleGroupStatisticsQueryBuilder())->getSql();
    }

    protected function buildResultSetMapping()
    {
        $rsm = $this->getResultSetMapping();

        $rsm->addScalarResult('totalTime', 'totalTime')
            ->addScalarResult('failedCount', 'failedCount')
            ->addScalarResult('totalCount', 'totalCount')
            ->addScalarResult('averageVehicleAgeInMonths', 'averageVehicleAgeInMonths')
            ->addScalarResult('siteName', 'siteName');

        return $rsm;
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
            ->setSiteName($row['siteName'])
        ;

        return $dbResult;
    }
}
