<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Site\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\Repository\ComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Site\QueryBuilder\SiteAverageComponentStatisticsQueryBuilder;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class SiteAverageComponentStatisticsRepository extends ComponentStatisticsRepository implements AutoWireableInterface
{
    const PARAM_SITE_ID = 'siteId';

    public function getComponentStatistics(int $siteId, string $group, LastMonthsDateRange $monthRange)
    {
        $qb = new SiteAverageComponentStatisticsQueryBuilder();

        $this->setMonthsRangeConfiguration($monthRange);

        return $this->getResult($qb->getComponentStatisticsSql(), [
            self::PARAM_SITE_ID => $siteId,
            ComponentStatisticsRepository::PARAM_GROUP => $group,
            ComponentStatisticsRepository::PARAM_START_DATE => $this->startDate,
            ComponentStatisticsRepository::PARAM_END_DATE => $this->endDate,
        ]);
    }

    public function getTotalCount(int $siteId, string $group, LastMonthsDateRange $monthRange)
    {
        $qb = new SiteAverageComponentStatisticsQueryBuilder();
        $this->setMonthsRangeConfiguration($monthRange);
        $sql = $qb->getSqlForTotalCountSql();
        $rsm = $this->getResultSetMappingForTotalCount();

        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameter(self::PARAM_SITE_ID, $siteId);
        $query->setParameter(ComponentStatisticsRepository::PARAM_GROUP, $group);
        $query->setParameter(ComponentStatisticsRepository::PARAM_START_DATE, $this->startDate);
        $query->setParameter(ComponentStatisticsRepository::PARAM_END_DATE, $this->endDate);

        $result = $query->getScalarResult();
        return $this->mapResultForTotalCount($result);
    }

    protected function getResultSetMappingForTotalCount()
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('failedCount', 'failedCount');

        return $rsm;
    }


    protected function mapResultForTotalCount($scalarResult)
    {
        foreach ($scalarResult as $row) {
            if (!empty($row['failedCount']))
                return (int) $row['failedCount'];
        }

        return 0;
    }


}
