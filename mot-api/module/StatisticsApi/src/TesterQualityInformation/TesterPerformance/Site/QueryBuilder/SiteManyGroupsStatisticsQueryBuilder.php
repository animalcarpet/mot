<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Site\QueryBuilder;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryBuilder\ManyGroupsStatisticsQueryBuilder;

class SiteManyGroupsStatisticsQueryBuilder extends ManyGroupsStatisticsQueryBuilder
{
    protected $index = "USE INDEX (`mot_test_site_id_started_date_completed_date_idx`)";
    protected $where = "AND `vts`.`id` = :siteId ";
}