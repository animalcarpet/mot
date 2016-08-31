<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Site\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\Repository\ManyGroupsStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Site\QueryBuilder\SiteManyGroupsStatisticsQueryBuilder;

class SiteManyGroupsStatisticsRepository extends ManyGroupsStatisticsRepository
{
    const PARAM_SITE_ID = "siteId";

    public function get($siteId, $year, $month)
    {
        return parent::getByParams([
            self::PARAM_SITE_ID => $siteId,
            self::PARAM_YEAR    => $year,
            self::PARAM_MONTH   => $month,
        ]);
    }

    protected function getSql()
    {
        return (new SiteManyGroupsStatisticsQueryBuilder())->getSql();
    }
}