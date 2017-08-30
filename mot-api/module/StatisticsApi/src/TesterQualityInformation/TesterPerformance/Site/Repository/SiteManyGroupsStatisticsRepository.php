<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Site\Repository;

use DvsaCommon\Date\LastMonthsDateRange;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\Repository\ManyGroupsStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Site\QueryBuilder\SiteManyGroupsStatisticsQueryBuilder;
use DvsaCommon\Enum\SiteBusinessRoleCode;

class SiteManyGroupsStatisticsRepository extends ManyGroupsStatisticsRepository
{
    const PARAM_SITE_ID = 'siteId';

    protected function getByParams(array $params)
    {
        $this->setMonthsRangeConfiguration($params[self::PARAM_MONTH]);

        $rsm = $this->buildResultSetMapping();

        $sql = $this->getSql();

        $query = $this->getNativeQuery($sql, $rsm)
            ->setParameters($params)
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate)
            ->setParameter('roleCode', SiteBusinessRoleCode::TESTER);

        $scalarResult = $query->getScalarResult();

        return $this->buildResult($scalarResult);
    }

    public function get($siteId, LastMonthsDateRange $rangeDateProvider)
    {
        return $this->getByParams([
            self::PARAM_SITE_ID => $siteId,
            self::PARAM_MONTH => $rangeDateProvider,
        ]);
    }

    protected function getSql()
    {
        return (new SiteManyGroupsStatisticsQueryBuilder())->getSql();
    }

    /**
     * @param LastMonthsDateRange $lastMonthsDateRange
     */
    protected function setMonthsRangeConfiguration(LastMonthsDateRange $lastMonthsDateRange)
    {
        $this->startDate = $lastMonthsDateRange->getStartDate();
        $this->endDate = $lastMonthsDateRange->getEndDate();
    }
}
