<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryBuilder;

class TesterAtSiteSingleGroupStatisticsQueryBuilder
{
    public function getSql()
    {
        return 'SELECT
            `vts`.`name` `siteName`,
            ifnull(test_stats.totalTime, 0) totalTime,
            ifnull(test_stats.failedCount,0) failedCount,
            ifnull(test_stats.totalCount,0) totalCount,
            ifnull(test_stats.averageVehicleAgeInMonths,NULL) averageVehicleAgeInMonths
          FROM (
              SELECT
                    distinct sbrm.site_id
                  FROM site_business_role sbr
                  JOIN site_business_role_map sbrm         ON sbr.id = sbrm.site_business_role_id
                  JOIN auth_for_testing_mot_at_site aftmas ON sbrm.`site_id` = aftmas.`site_id`
                  JOIN auth_for_testing_mot a              ON sbrm.person_id = a.person_id
                  JOIN vehicle_class vc                    ON vc.id = a.vehicle_class_id
                  JOIN vehicle_class_group vcg             ON vcg.id = vc.vehicle_class_group_id
                  WHERE
                    sbrm.`person_id` = :testerId
                    AND sbrm.`site_id` = :vtsId
                    AND sbr.code = :roleCode
                    AND vcg.code = :groupCode
                    AND aftmas.`vehicle_class_id` = a.`vehicle_class_id`
              UNION
                SELECT
                      distinct site_id
                    FROM tqi_test_count
                    JOIN site site ON 
                      site.id = tqi_test_count.site_id
                      AND site.organisation_id = tqi_test_count.organisation_id
                  JOIN vehicle_class_group vcg             ON vcg.id = vehicle_class_group_id
                    WHERE
                      period_start_date BETWEEN :startDate AND :endDate
                      AND person_id = :testerId
                      AND site_id = :vtsId
                      AND vcg.code = :groupCode
          ) sites
          JOIN site vts ON vts.id = sites.site_id
          LEFT JOIN (
              SELECT
                  site_id,
                  sum(total_time)  totalTime ,
                  sum(failed_count) failedCount,
                  sum(total_count)  totalCount,
                  coalesce(SUM(vehicle_age_sum),0) / coalesce(SUM(vehicles_with_manufacture_date_count),1) averageVehicleAgeInMonths
                FROM tqi_test_count
                JOIN site site ON 
                  site.id = tqi_test_count.site_id
                  AND site.organisation_id = tqi_test_count.organisation_id
                JOIN vehicle_class_group vcg             ON vcg.id = vehicle_class_group_id
                WHERE
                  period_start_date BETWEEN :startDate AND :endDate
                  AND person_id = :testerId
                  AND site_id = :vtsId
                  AND vcg.code = :groupCode
                GROUP BY site_id
            ) test_stats ON sites.site_id = test_stats.site_id
            ORDER BY `totalCount` DESC, siteName';
    }
}
