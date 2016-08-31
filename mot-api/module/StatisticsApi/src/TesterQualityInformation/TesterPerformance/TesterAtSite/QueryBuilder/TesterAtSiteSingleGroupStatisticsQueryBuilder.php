<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryBuilder;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\QueryBuilder\TesterPerformanceQueryBuilder;

class TesterAtSiteSingleGroupStatisticsQueryBuilder extends TesterPerformanceQueryBuilder
{
    protected $index = "USE INDEX (`mot_test_person_id_started_date_completed_date_idx`)";
    protected $where = "AND `person`.`id` = :testerId AND vts.id = :vtsId AND `class_group`.`code` = :groupCode";
    protected $selectFields = "vts.name as siteName, ";
}