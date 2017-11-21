<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Site\QueryBuilder;

class SiteAverageComponentStatisticsQueryBuilder
{
    public function getComponentStatisticsSql()
    {
        return 'SELECT
            failed_count failedCount,
            category_names.id testItemCategoryId,
            category_names.name testItemCategoryName
          FROM (
	        SELECT stats.site_id, 
                  stats.organisation_id,
                  stats.person_id, 
                  vcg.code vehicle_class_group,
                  sum(stats.failed_count) failed_count,
                  stats.test_item_category_id
	          FROM tqi_rfr_count stats                  
	          JOIN vehicle_class_group vcg             ON vcg.id = stats.vehicle_class_group_id
              WHERE
		        stats.site_id = :siteId
                AND stats.organisation_id = (
                  SELECT organisation_id 
                    FROM site 
                    WHERE id = :siteId)
                AND vcg.code = :groupCode
                AND stats.period_start_date >= :startDate
                AND stats.period_start_date < :endDate 
              GROUP BY stats.site_id, 
                  stats.organisation_id,
                  stats.vehicle_class_group_id,
                  stats.test_item_category_id
	        ) x
            RIGHT JOIN (
			   SELECT
				      tic.id      `id`,
				     `language_map`.name `name`,
                      vcg.code group_code
			   FROM test_item_category tic
				 JOIN ti_category_language_content_map `language_map` ON tic.id = `language_map`.test_item_category_id
				 JOIN test_item_category_vehicle_class_map `item_class_map` ON `item_class_map`.test_item_category_id = tic.id
				 JOIN vehicle_class vc ON vc.id = `item_class_map`.vehicle_class_id
				 JOIN vehicle_class_group vcg ON vc.`vehicle_class_group_id` = vcg.id
				 JOIN language_type lt ON `language_map`.language_lookup_id = lt.id
			   WHERE lt.code = :languageTypeCode
					 AND tic.parent_test_item_category_id = 0
					 -- removing parent test item category and \'Items not tested\' that is not appearing in frontend
					 AND tic.id NOT IN (0, 5800, 10000)
					 AND vcg.code = :groupCode
					 AND (tic.end_date is null OR tic.end_date > :startDate)
                     AND tic.start_date <= :endDate
			   GROUP BY id, vcg.code 
			 ) category_names ON x.test_item_category_id = category_names.id and category_names.group_code = x.vehicle_class_group
          GROUP BY x.site_id, x.organisation_id, x.person_id, category_names.id
          ORDER BY category_names.name';
    }

    public function getSqlForTotalCountSql()
    {
        return 'SELECT
                  sum(failed_count) failedCount
                FROM tqi_test_count
                JOIN vehicle_class_group vcg ON vcg.id = vehicle_class_group_id
                WHERE
                  period_start_date BETWEEN :startDate AND :endDate
                  AND site_id = :siteId
                  AND vcg.code = :groupCode
                  AND organisation_id = (
                    SELECT organisation_id
                      FROM site
                      WHERE id = :siteId )';
    }
}
