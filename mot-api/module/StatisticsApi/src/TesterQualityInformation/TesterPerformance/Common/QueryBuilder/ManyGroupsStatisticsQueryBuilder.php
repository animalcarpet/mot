<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryBuilder;

class ManyGroupsStatisticsQueryBuilder extends TesterPerformanceQueryBuilder
{
    protected $selectFields = '`class_group`.`code` `vehicleClassGroup`,
                               `person`.`id` `person_id`,
                               `person`.`username` `username`,
                               `person`.`first_name` `firstName`, 
                               `person`.`family_name` `familyName`, 
                               `person`.`middle_name` `middleName`, ';

    protected $groupBy = 'GROUP BY `person` . `id`, `class_group` . `code`
                          ORDER BY `totalCount` DESC';
}
