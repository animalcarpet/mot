<?php

namespace DvsaEntities\Repository;

use DateTime;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\LanguageTypeCode;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;

/**
 * A repository for TQI RFR counts.
 *
 * @codeCoverageIgnore
 */
class TqiRfrCountRepository extends AbstractMutableRepository
{
    public function checkIfThereAreDataForPeriod(DateTime $startDate, DateTime $endDate): int
    {
        $query = $this->createQueryBuilder('tqiRfr');
        $query
            ->select('count(tqiRfr.id)')
            ->where('tqiRfr.periodStartDate >= :start_date')
            ->andWhere('tqiRfr.periodStartDate <= :end_date')
            ->setParameter('start_date', DateTimeApiFormat::dateTime($startDate))
            ->setParameter('end_date', DateTimeApiFormat::dateTime($endDate));

        return (int)$query->getQuery()->getSingleScalarResult();
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return bool success or fail of the query
     * @throws \Throwable
     */
    public function populateTableWithData(DateTime $startDate, DateTime $endDate)
    {
        $query = "
            insert into tqi_rfr_count
            (
                `period_start_date`,
                `site_id`          ,
                `organisation_id`  ,
                `person_id`        ,
                `vehicle_class_group_id` ,
                `test_item_category_id`,
                `failed_count`
            )
            SELECT
                :start_date,
                test.site_id, 
                test.organisation_id,
                test.person_id, 
                class_group.id group_code,
                tic.section_test_item_category_id test_item_category_id,
                COUNT(DISTINCT test.id) failed_count
            FROM mot_test_current test USE INDEX (`ix_mot_test_current_completed_date`)
            JOIN mot_test_current_rfr_map `rfr_map` ON `rfr_map`.mot_test_id = test.id
            JOIN reason_for_rejection_type rfr_type ON rfr_type.id = rfr_map.rfr_type_id
            JOIN reason_for_rejection rfr ON rfr.id = `rfr_map`.rfr_id
            JOIN test_item_category tic ON rfr.test_item_category_id = tic.id
            JOIN ti_category_language_content_map `language_map` ON `language_map`.test_item_category_id = tic.section_test_item_category_id
            JOIN language_type lt ON `language_map`.language_lookup_id = lt.id
            JOIN mot_test_type type ON type.id = test.mot_test_type_id
            JOIN mot_test_status status ON status.id = test.status_id
            JOIN vehicle ON vehicle.id = test.vehicle_id
            JOIN model_detail md ON md.id = vehicle.model_detail_id
            JOIN vehicle_class class ON class.id = md.vehicle_class_id
            JOIN vehicle_class_group class_group ON class_group.id = class.vehicle_class_group_id
            LEFT JOIN mot_test_emergency_reason mter ON mter.id = test.id
            WHERE 
                test.completed_date >= :start_date 
                AND test.completed_date <= :end_date
                AND status.code = :test_status_code
                AND type.code IN (:test_type_normal, :test_type_mystery_shopper)
                AND lt.code = :language_code
                AND mter.emergency_log_id IS NULL
                AND rfr_type.name NOT IN (:rfr_advisory, :rfr_non_specific, :rfr_user_entered)
            GROUP BY 
                test.site_id, 
                test.organisation_id, 
                test.person_id, 
                class_group.code,
                test_item_category_id;
        ";

        $stmt = $this->_em->getConnection()->prepare($query);
        $stmt->bindValue("start_date", $startDate->format(DateUtils::FORMAT_ISO_WITH_TIME));
        $stmt->bindValue("end_date", $endDate->format(DateUtils::FORMAT_ISO_WITH_TIME));
        $stmt->bindValue("test_status_code", MotTestStatusCode::FAILED);
        $stmt->bindValue("test_type_normal", MotTestTypeCode::NORMAL_TEST);
        $stmt->bindValue("test_type_mystery_shopper", MotTestTypeCode::MYSTERY_SHOPPER);
        $stmt->bindValue("language_code", LanguageTypeCode::ENGLISH);
        $stmt->bindValue("rfr_advisory", ReasonForRejectionTypeName::ADVISORY);
        $stmt->bindValue("rfr_non_specific", ReasonForRejectionTypeName::NON_SPECIFIC);
        $stmt->bindValue("rfr_user_entered", ReasonForRejectionTypeName::USER_ENTERED);

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

    public function deleteStatsOlderThan(DateTime $maxPeriodStartDate)
    {
        $query = $this->createQueryBuilder('tqiRfr');
        $query
            ->delete()
            ->where('tqiRfr.periodStartDate < :max_period_start_date')
            ->setParameter('max_period_start_date', DateTimeApiFormat::dateTime($maxPeriodStartDate));

        return $query->getQuery()->execute();
    }
}