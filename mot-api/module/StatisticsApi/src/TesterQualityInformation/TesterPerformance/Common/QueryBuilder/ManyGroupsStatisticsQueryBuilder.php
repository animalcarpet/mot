<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryBuilder;

class ManyGroupsStatisticsQueryBuilder extends TesterPerformanceQueryBuilder
{
    protected $selectFields = "`class_group`.`code` `vehicleClassGroup`,
                               `person`.`id` `person_id`,
                               `person`.`username` `username`,";

    protected $groupBy = "GROUP BY `person` . `id`, `class_group` . `code`
                          ORDER BY `totalCount` DESC";
}