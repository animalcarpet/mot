<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Repository\AbstractStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryBuilder\TesterPerformanceQueryBuilder;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterPerformanceResult;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\OrganisationSiteStatusCode;

class SingleGroupStatisticsRepository extends AbstractStatisticsRepository
{
    const PARAM_YEAR = "year";
    const PARAM_MONTH = "month";
    const PARAM_GROUP_CODE = "groupCode";

    protected function getByParams($params)
    {
        $rsm = $this->buildResultSetMapping();

        $this->setDaysConfiguration($params[self::PARAM_YEAR], $params[self::PARAM_MONTH]);

        $query = $this->getNativeQuery($this->getSql(), $rsm)
            ->setParameters($params)
            ->setParameter('failedStatusCode', MotTestStatusCode::FAILED)
            ->setParameter('passStatusCode', MotTestStatusCode::PASSED)
            ->setParameter('normalTestCode', MotTestTypeCode::NORMAL_TEST)
            ->setParameter('mysteryShopperTestCode', MotTestTypeCode::MYSTERY_SHOPPER)
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endData', $this->endDate)
            ->setParameter('groupCode', $params[self::PARAM_GROUP_CODE])
            ->setParameter('irrelevantAssociationCodes',
                [
                    OrganisationSiteStatusCode::APPLIED,
                    OrganisationSiteStatusCode::UNKNOWN
                ]
            );

        $scalarResult = $query->getScalarResult();
        $row = $scalarResult[0];

        return $this->createTesterPerformanceResult($row);
    }

    protected function buildResultSetMapping()
    {
        return $this->getResultSetMapping()
            ->addScalarResult('totalTime', 'totalTime')
            ->addScalarResult('failedCount', 'failedCount')
            ->addScalarResult('totalCount', 'totalCount')
            ->addScalarResult('averageVehicleAgeInMonths', 'averageVehicleAgeInMonths');
    }

    protected function getSql()
    {
        return (new TesterPerformanceQueryBuilder())->getSql();
    }

    protected function createTesterPerformanceResult(array $row)
    {
        $dbResult = new TesterPerformanceResult();
        $dbResult->setTotalTime((double)$row['totalTime'])
            ->setFailedCount((int)$row['failedCount'])
            ->setAverageVehicleAgeInMonths((float)$row['averageVehicleAgeInMonths'])
            ->setIsAverageVehicleAgeAvailable(!is_null($row['averageVehicleAgeInMonths']))
            ->setTotalCount((int)$row ['totalCount']);

        return $dbResult;
    }
}
