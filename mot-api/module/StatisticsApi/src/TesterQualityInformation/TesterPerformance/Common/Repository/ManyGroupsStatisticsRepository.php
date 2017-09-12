<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Repository\AbstractStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterAtSitePerformanceResult;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryBuilder\ManyGroupsStatisticsQueryBuilder;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\OrganisationSiteStatusCode;

class ManyGroupsStatisticsRepository extends AbstractStatisticsRepository
{
    const PARAM_YEAR = 'year';
    const PARAM_MONTH = 'month';

    protected function getByParams(array $params)
    {
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
        $testerPerformanceResult = new TesterAtSitePerformanceResult();

        $testerPerformanceResult
            ->setPersonId($row['person_id'])
            ->setUsername($row['username'])
            ->setFirstName($row['firstName'])
            ->setMiddleName($row['middleName'])
            ->setFamilyName($row['familyName'])
            ->setAverageVehicleAgeInMonths((float) $row['averageVehicleAgeInMonths'])
            ->setVehicleClassGroup($row['vehicleClassGroup'])
            ->setTotalTime($row['totalTime'])
            ->setIsAverageVehicleAgeAvailable(!is_null($row['averageVehicleAgeInMonths']))
            ->setTotalCount($row ['totalCount'])
            ->setFailedCount($row['failedCount'])
        ;

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
            ->addScalarResult('firstName', 'firstName')
            ->addScalarResult('middleName', 'middleName')
            ->addScalarResult('familyName', 'familyName')
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
