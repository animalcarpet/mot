<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Site\QueryBuilder;

class SiteManyGroupsStatisticsQueryBuilder
{
    public function getSql()
    {
        return 'SELECT
            vcg.`code` vehicleClassGroup,
            persons.person_id person_id,
            `p`.`username` `username`,
            `p`.`first_name` `firstName`,
            `p`.`family_name` `familyName`,
            `p`.`middle_name` `middleName`,
            ifnull(test_stats.totalTime, 0) totalTime,
            ifnull(test_stats.failedCount,0) failedCount,
            ifnull(test_stats.totalCount,0) totalCount,
            ifnull(test_stats.averageVehicleAgeInMonths, NULL) averageVehicleAgeInMonths
          FROM (
              SELECT
                    distinct sbrm.person_id,
                    vc.vehicle_class_group_id vehicleClassGroupId
                  FROM site_business_role sbr
                  JOIN site_business_role_map sbrm         ON sbr.id = sbrm.site_business_role_id
                  JOIN auth_for_testing_mot_at_site aftmas ON sbrm.`site_id` = aftmas.`site_id`
                  JOIN auth_for_testing_mot a              ON sbrm.person_id = a.person_id
                  JOIN vehicle_class vc                    ON vc.id = a.vehicle_class_id
                  WHERE
                    sbrm.`site_id` = :siteId
                    AND sbr.code = :roleCode
                    AND aftmas.`vehicle_class_id` = a.`vehicle_class_id`
              UNION
                SELECT
                      distinct person_id,
                      vehicle_class_group_id vehicleClassGroupId
                    FROM tqi_test_count
                    WHERE
                      period_start_date BETWEEN :startDate AND :endDate
                      AND site_id = :siteId
                      AND organisation_id = (
                        SELECT organisation_id
                          FROM site
                          WHERE id = :siteId )
          ) persons
          JOIN person p ON p.id = persons.person_id
          JOIN vehicle_class_group vcg ON vcg.id = persons.vehicleClassGroupId
          LEFT JOIN (
              SELECT
                  person_id,
                  tqi_test_count.`vehicle_class_group_id` vehicleClassGroupId ,
                  sum(total_time)  totalTime ,
                  sum(failed_count) failedCount,
                  sum(total_count)  totalCount,
                  coalesce(SUM(vehicle_age_sum),0) / coalesce(SUM(vehicles_with_manufacture_date_count),1) averageVehicleAgeInMonths
                FROM tqi_test_count
                WHERE
                  period_start_date BETWEEN :startDate AND :endDate
                  AND site_id = :siteId
                  AND organisation_id = (
                    SELECT organisation_id
                      FROM site
                      WHERE id = :siteId )
                GROUP BY person_id, vehicleClassGroupId
            ) test_stats ON persons.person_id = test_stats.person_id
              AND persons.vehicleClassGroupId = test_stats.vehicleClassGroupId
            ORDER BY `totalCount` DESC, persons.person_id, vehicleClassGroup';
    }
}
