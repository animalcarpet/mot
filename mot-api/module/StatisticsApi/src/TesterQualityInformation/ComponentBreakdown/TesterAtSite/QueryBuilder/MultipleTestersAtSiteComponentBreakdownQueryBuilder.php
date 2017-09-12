<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\QueryBuilder;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryBuilder\ComponentBreakdownQueryBuilder;

class MultipleTestersAtSiteComponentBreakdownQueryBuilder extends ComponentBreakdownQueryBuilder
{
    public function getSql()
    {
        return '
            SELECT p.id personId, 
                c.id testItemCategoryId, 
                c.name testItemCategoryName, 
                s.failed_count failedCount
            FROM person AS p

            JOIN (SELECT
                    tic.id,
                    language_map.name,
                    vcg.code group_code
                FROM test_item_category tic
                JOIN ti_category_language_content_map `language_map` ON tic.id = `language_map`.test_item_category_id
                JOIN test_item_category_vehicle_class_map `item_class_map` ON `item_class_map`.test_item_category_id = tic.id
                JOIN vehicle_class vc ON vc.id = `item_class_map`.vehicle_class_id
                JOIN vehicle_class_group vcg ON vc.`vehicle_class_group_id` = vcg.id
                JOIN language_type lt ON `language_map`.language_lookup_id = lt.id
                WHERE lt.code = \'EN\'
                    AND tic.parent_test_item_category_id = 0
                    -- removing parent test item category and \'Items not tested\' that is not appearing in frontend
                    AND tic.id NOT IN (0, 5800, 10000)
                    AND vcg.code = :groupCode
                GROUP BY id, vcg.code  
            ) AS c
                           
            LEFT JOIN (
                SELECT stats.site_id, 
                       stats.organisation_id,
                       stats.person_id,
                       stats.test_item_category_id,
                       vcg.code vehicle_class_group,
                       sum(stats.failed_count) failed_count
                FROM tqi_rfr_count stats                  
                JOIN vehicle_class_group vcg 
                    ON vcg.id = stats.vehicle_class_group_id
                WHERE stats.site_id = :siteId
                    AND stats.organisation_id = (
                        SELECT organisation_id 
                        FROM site 
                        WHERE id = :siteId)
                            AND vcg.code = :groupCode
                            AND stats.period_start_date >= :startDate
                            AND stats.period_start_date < :endDate 
                        GROUP BY stats.site_id,
                           stats.organisation_id,
                           stats.person_id,
                           stats.vehicle_class_group_id,
                           stats.test_item_category_id
            ) AS s ON s.person_id = p.id AND s.test_item_category_id = c.id

            WHERE s.site_id = :siteId
                     
            GROUP BY p.id, c.id
            ORDER BY p.id, c.name;
        ';
    }
}
