<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Repository\AbstractStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryBuilder\ManyGroupsStatisticsQueryBuilder;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterPerformanceResult;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\OrganisationSiteStatusCode;

class ManyGroupsStatisticsRepository extends AbstractStatisticsRepository
{
    const PARAM_YEAR = 'year';
    const PARAM_MONTH = 'month';

    protected function getByParams(array $params)
    {
        $this->setDaysConfiguration($params[self::PARAM_YEAR], $params[self::PARAM_MONTH]);

        $rsm = $this->buildResultSetMapping();

        $sql = $this->getSql();

        $query = $this->getNativeQuery($sql, $rsm)
            ->setParameters($params)
            ->setParameter('failedStatusCode', MotTestStatusCode::FAILED)
            ->setParameter('passStatusCode', MotTestStatusCode::PASSED)
            ->setParameter('normalTestCode', MotTestTypeCode::NORMAL_TEST)
            ->setParameter('mysteryShopperTestCode', MotTestTypeCode::MYSTERY_SHOPPER)
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endData', $this->endDate)
            ->setParameter('irrelevantAssociationCodes',
                [
                    OrganisationSiteStatusCode::APPLIED,
                    OrganisationSiteStatusCode::UNKNOWN,
                ]
            );

        $scalarResult = $query->getScalarResult();

        return $this->buildResult($scalarResult);
    }

    protected function createResultRow($row)
    {
        $testerPerformanceResult = new TesterPerformanceResult();

        $testerPerformanceResult->setVehicleClassGroup($row['vehicleClassGroup'])
            ->setPersonId($row['person_id'])
            ->setUsername($row['username'])
            ->setTotalTime($row['totalTime'])
            ->setAverageVehicleAgeInMonths((float) $row['averageVehicleAgeInMonths'])
            ->setIsAverageVehicleAgeAvailable(!is_null($row['averageVehicleAgeInMonths']))
            ->setFailedCount($row['failedCount'])
            ->setTotalCount($row ['totalCount']);

        return $testerPerformanceResult;
    }

    protected function getSql()
    {
        return (new ManyGroupsStatisticsQueryBuilder())->getSql();
    }

    protected function buildResultSetMapping()
    {
        return $this->getResultSetMapping()
            ->addScalarResult('vehicleClassGroup', 'vehicleClassGroup')
            ->addScalarResult('person_id', 'person_id')
            ->addScalarResult('username', 'username')
            ->addScalarResult('totalTime', 'totalTime')
            ->addScalarResult('failedCount', 'failedCount')
            ->addScalarResult('totalCount', 'totalCount')
            ->addScalarResult('averageVehicleAgeInMonths', 'averageVehicleAgeInMonths');
    }

    protected function buildResult($scalarResult)
    {
        $dbResults = [];
        foreach ($scalarResult as $row) {
            $dbResults[] = $this->createResultRow($row);
        }

        return $dbResults;
    }
}
