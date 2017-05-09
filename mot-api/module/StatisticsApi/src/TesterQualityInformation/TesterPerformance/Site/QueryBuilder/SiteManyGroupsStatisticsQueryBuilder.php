<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Site\QueryBuilder;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryBuilder\ManyGroupsStatisticsQueryBuilder;

class SiteManyGroupsStatisticsQueryBuilder extends ManyGroupsStatisticsQueryBuilder
{
    protected $index = 'USE INDEX (`ix_mot_test_current_site_id_started_date_completed_date`)';
    protected $where = 'AND `vts`.`id` = :siteId ';
}
