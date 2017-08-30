<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\Repository\ManyGroupsStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\QueryBuilder\TesterManyGroupsStatisticsQueryBuilder;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TesterManyGroupsStatisticsRepository extends ManyGroupsStatisticsRepository implements AutoWireableInterface
{
    const PARAM_TESTER_ID = 'testerId';

    public function get(int $testerId, LastMonthsDateRange $monthRange)
    {
        $this->setMonthsRangeConfiguration($monthRange);

        return $this->getByParams([
            self::PARAM_TESTER_ID => $testerId,
        ]);
    }

    protected function getByParams(array $params)
    {
        $rsm = $this->buildResultSetMapping();

        $sql = $this->getSql();

        $query = $this->getNativeQuery($sql, $rsm)
            ->setParameters($params)
            ->setParameter('roleCode', SiteBusinessRoleCode::TESTER)
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate);

        $scalarResult = $query->getScalarResult();

        return $this->buildResult($scalarResult);
    }

    protected function getSql()
    {
        return (new TesterManyGroupsStatisticsQueryBuilder())->getSql();
    }
}
