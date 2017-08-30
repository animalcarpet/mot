<?php

namespace DvsaEntities\Repository;

use DateTime;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestTypeCode;

/**
 * A repository for TQI tests counts.
 *
 * @codeCoverageIgnore
 */
class TqiTestCountRepository extends AbstractMutableRepository
{
    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return bool success or fail of the query
     * @throws \Throwable
     */
    public function populateTableWithData(DateTime $startDate, DateTime $endDate)
    {
        $query = "
            INSERT INTO tqi_test_count (
                period_start_date,
                site_id, 
                organisation_id,
                person_id,
                vehicle_class_group_id,
                total_time,
                failed_count,
                total_count ,
                vehicle_age_sum,
                vehicles_with_manufacture_date_count
            )
            SELECT
                :start_date,
                `test`.site_id,
                `test`.organisation_id,
                `test`.person_id,
                `class_group`.`id`,
                SUM(TIMESTAMPDIFF(SECOND, `test`.`started_date`, `test`.`completed_date`))  `totalTime`,
                SUM(IF(`status`.`code` = :test_status_code_failed, 1, 0)) `failedCount`,
                COUNT(`test`.`id`) `totalCount`,
                SUM(TIMESTAMPDIFF(MONTH,`v`.`manufacture_date`,`test`.`completed_date`)),
                COUNT(`v`.`manufacture_date`)
            FROM `mot_test_current` `test` USE INDEX (`ix_mot_test_current_completed_date`)
            JOIN `vehicle` `v` ON `test`.`vehicle_id` = `v`.`id` 
            JOIN `mot_test_type` `type` ON `type`.`id` = `test`.`mot_test_type_id`
            JOIN `mot_test_status` `status` ON `status`.`id` = `test`.`status_id`
            JOIN `model_detail` `md` ON `md`.`id` = `v`.`model_detail_id`
            JOIN `vehicle_class` `class` ON `class`.`id` = `md`.`vehicle_class_id`
            JOIN `vehicle_class_group` `class_group` ON `class_group`.`id` = `class`.`vehicle_class_group_id`
            LEFT JOIN mot_test_emergency_reason mter ON mter.id = test.id
            WHERE 
                `test`.`completed_date` >= :start_date 
                AND `test`.`completed_date` <= :end_date
                AND (`status`.`code` = :test_status_code_failed OR (`status`.`code` = :test_status_code_passed AND `test`.`prs_mot_test_id` IS NULL))
                AND (`type`.`code` = :test_type_normal OR `type`.`code` = :test_type_mystery_shopper)
                AND `emergency_log_id` IS NULL
            GROUP BY 
                1,
                `test`.site_id,
                `test`.organisation_id,
                `test`.person_id,
                `class_group`.`code`
        ";

        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue("start_date", $startDate->format(DateUtils::FORMAT_ISO_WITH_TIME));
        $stmt->bindValue("end_date", $endDate->format(DateUtils::FORMAT_ISO_WITH_TIME));
        $stmt->bindValue("test_status_code_failed", MotTestStatusCode::FAILED);
        $stmt->bindValue("test_status_code_passed", MotTestStatusCode::PASSED);
        $stmt->bindValue("test_type_normal", MotTestTypeCode::NORMAL_TEST);
        $stmt->bindValue("test_type_mystery_shopper", MotTestTypeCode::MYSTERY_SHOPPER);

        try {
            $this->_em->beginTransaction();
            $result = $stmt->execute();
            $this->_em->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->_em->rollback();
            throw $e;
        }
    }

    public function checkIfThereAreDataForPeriod(DateTime $startDate, DateTime $endDate)
    {
        $query = $this->createQueryBuilder('testCounts');
        $query
            ->select('count(testCounts.id)')
            ->where('testCounts.periodStartDate >= :start_date')
            ->andWhere('testCounts.periodStartDate <= :end_date')
            ->setParameter('start_date', DateTimeApiFormat::dateTime($startDate))
            ->setParameter('end_date', DateTimeApiFormat::dateTime($endDate));

        return (int)$query->getQuery()->getSingleScalarResult();
    }

    public function deleteStatsOlderThan(DateTime $maxPeriodStartDate)
    {
        $query = $this->createQueryBuilder('testCounts');
        $query
            ->delete()
            ->where('testCounts.periodStartDate < :max_period_start_date')
            ->setParameter('max_period_start_date', DateTimeApiFormat::dateTime($maxPeriodStartDate));

        $query->getQuery()->execute();

        return;
    }
}