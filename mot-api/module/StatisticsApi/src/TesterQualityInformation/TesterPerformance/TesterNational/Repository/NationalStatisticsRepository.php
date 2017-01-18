<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Repository\AbstractStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\QueryResult\NationalStatisticsResult;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\ReasonForRejection;

class NationalStatisticsRepository extends AbstractStatisticsRepository implements AutoWireableInterface
{
    public function getStatistics($year, $month)
    {
        $rsm = $this->getResultSetMapping()
            ->addScalarResult('totalACount', 'totalACount')
            ->addScalarResult('totalAFailCount', 'totalAFailCount')
            ->addScalarResult('totalATestTime', 'totalATestTime')
            ->addScalarResult('totalAVehicleAgeInMonths', 'totalAVehicleAgeInMonths')
            ->addScalarResult('totalBCount', 'totalBCount')
            ->addScalarResult('totalBFailCount', 'totalBFailCount')
            ->addScalarResult('totalBTestTime', 'totalBTestTime')
            ->addScalarResult('totalBVehicleAgeInMonths', 'totalBVehicleAgeInMonths');

        $this->setDaysConfiguration($year, $month);

        $query = $this->getNativeQuery($this->getSqlForStatistics(), $rsm)
            ->setParameter('normalTestCode', MotTestTypeCode::NORMAL_TEST)
            ->setParameter('mysteryShopperTestCode', MotTestTypeCode::MYSTERY_SHOPPER)
            ->setParameter('passStatusCode', MotTestStatusCode::PASSED)
            ->setParameter('failedStatusCode', MotTestStatusCode::FAILED)
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate)
            ->setParameter('groupACode', VehicleClassGroupCode::BIKES)
            ->setParameter('skippedRfrTypes', ReasonForRejection::getTestQualityInformationSkippedRfrTypes())
            ->setParameter('groupBCode', VehicleClassGroupCode::CARS_ETC);

        $dbResult = $query->getScalarResult()[0];

        $numberOfGroupATesters = $this->getNumberOfTestersForGroup(VehicleClassGroupCode::BIKES);
        $numberOfGroupBTesters = $this->getNumberOfTestersForGroup(VehicleClassGroupCode::CARS_ETC);

        $result = new NationalStatisticsResult();
        $result
            ->setGroupATotal((int)$dbResult['totalACount'] ?: 0)
            ->setGroupACumulativeTestTime((int)$dbResult['totalATestTime'])
            ->setGroupAFailed((int)$dbResult['totalAFailCount'] ?: 0)
            ->setGroupAAverageVehicleAgeInMonths((float)$dbResult['totalAVehicleAgeInMonths'])
            ->setIsGroupAAverageVehicleAgeAvailable(!is_null($dbResult['totalAVehicleAgeInMonths']))
            ->setNumberOfGroupATesters((int)$numberOfGroupATesters)
            ->setGroupBTotal((int)$dbResult['totalBCount'] ?: 0)
            ->setGroupBCumulativeTestTime((int)$dbResult['totalBTestTime'])
            ->setGroupBFailed((int)$dbResult['totalBFailCount'] ?: 0)
            ->setGroupBAverageVehicleAgeInMonths((float)$dbResult['totalBVehicleAgeInMonths'])
            ->setIsGroupBAverageVehicleAgeAvailable(!is_null($dbResult['totalBVehicleAgeInMonths']))
            ->setNumberOfGroupBTesters((int)$numberOfGroupBTesters);

        return $result;
    }

    private function getNumberOfTestersForGroup($groupCode)
    {
        $rsm = $this->getResultSetMapping()
            ->addScalarResult('numberOfTesters', 'numberOfTesters');

        return $this->getNativeQuery($this->getSqlForNumberOfTesters(), $rsm)
            ->setParameter('normalTestCode', MotTestTypeCode::NORMAL_TEST)
            ->setParameter('mysteryShopperTestCode', MotTestTypeCode::MYSTERY_SHOPPER)
            ->setParameter('passStatusCode', MotTestStatusCode::PASSED)
            ->setParameter('failedStatusCode', MotTestStatusCode::FAILED)
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endDate', $this->endDate)
            ->setParameter('classGroupCode', $groupCode)
            ->getSingleScalarResult();
    }

    private function getSqlForNumberOfTesters()
    {
        return "SELECT COUNT(DISTINCT `test`.`person_id`) `numberOfTesters`
                  FROM `mot_test_current` `test` USE INDEX (`ix_mot_test_current_completed_date`)
                  JOIN `mot_test_status` `status` ON `status`.`id` = `test`.`status_id`
                  JOIN `mot_test_type` `type` ON `type`.`id` = `test`.`mot_test_type_id`
                  LEFT JOIN `vehicle` ON (`vehicle`.`id` = `test`.`vehicle_id`) AND (`vehicle`.`version` = `test`.`vehicle_version`)
                  LEFT JOIN `vehicle_hist` ON (`vehicle_hist`.`id` = `test`.`vehicle_id`) AND (`vehicle_hist`.`version` = `test`.`vehicle_version`)
                  JOIN `model_detail` `md` ON `md`.`id` = COALESCE (`vehicle`.`model_detail_id`, `vehicle_hist`.`model_detail_id`)        
                  JOIN `vehicle_class` `class` ON `class`.`id` = `md`.`vehicle_class_id`                  
                  JOIN `vehicle_class_group` `classGroup` ON `classGroup`.`id` = `class`.`vehicle_class_group_id`
                  LEFT JOIN mot_test_emergency_reason AS mter ON mter.id = test.id
                  WHERE `test`.`completed_date` BETWEEN :startDate AND :endDate
                  AND (`type`.`code` = :normalTestCode OR `type`.`code` = :mysteryShopperTestCode)
                  -- the only tests we take into account are failures or non PRS passed ones
                  AND (`status`.`code` = :failedStatusCode OR (`status`.`code` = :passStatusCode AND `test`.`prs_mot_test_id` IS NULL))
                  AND `mter`.`emergency_log_id` IS NULL
                  AND `classGroup`.`code` = :classGroupCode";
    }

    private function getSqlForStatistics()
    {
        return "SELECT
              sum(IF(`classGroup`.`code` = :groupACode, 1, 0))                                        `totalACount`,
              sum(IF(`classGroup`.`code` = :groupACode AND `status`.`code` = :failedStatusCode, 1, 0)) `totalAFailCount`,
              sum(IF(`classGroup`.`code` = :groupACode, TIMESTAMPDIFF(SECOND , `test`.`started_date`, `test`.`completed_date`), 0)) `totalATestTime`,
              -- count average age only for vehicles with manufacture date set
              AVG(IF(`classGroup`.`code` = 'A',
                  TIMESTAMPDIFF(MONTH,
                      COALESCE(`v`.`manufacture_date`,
                               `v_hist`.`manufacture_date`),
                      `test`.`completed_date`),
                  NULL)) AS `totalAVehicleAgeInMonths`,
              sum(IF(`classGroup`.`code` = :groupBCode, 1, 0))                                        `totalBCount`,
              sum(IF(`classGroup`.`code` = :groupBCode AND `status`.`code` = :failedStatusCode, 1, 0)) `totalBFailCount`,
              sum(IF(`classGroup`.`code` = :groupBCode, TIMESTAMPDIFF(SECOND , `test`.`started_date`, `test`.`completed_date`), 0)) `totalBTestTime`,
              AVG(
                  IF(`classGroup`.`code` = 'B', IF(COALESCE (`v`.`manufacture_date`, `v_hist`.`manufacture_date`) IS NOT NULL,
                     TIMESTAMPDIFF(MONTH, COALESCE (`v`.`manufacture_date`, `v_hist`.`manufacture_date`), `test`.`completed_date`),
                     NULL
                  ), NULL)
              ) AS `totalBVehicleAgeInMonths`
            FROM `mot_test_current` `test` USE INDEX (`ix_mot_test_current_completed_date`)
              LEFT JOIN `vehicle` `v` ON (`test`.`vehicle_id` = `v`.`id`) AND (`test`.`vehicle_version` = `v`.`version`)
              LEFT JOIN `vehicle_hist` `v_hist` ON (`test`.`vehicle_id` = `v_hist`.`id`) AND (`test`.`vehicle_version` = `v_hist`.`version`)
              JOIN `mot_test_status` `status` ON `status`.`id` = `test`.`status_id`
              JOIN `mot_test_type` `type` ON `type`.`id` = `test`.`mot_test_type_id`
              JOIN `model_detail` `md` ON `md`.`id` = COALESCE (`v`.`model_detail_id`, `v_hist`.`model_detail_id`)              
              JOIN `vehicle_class` `class` ON `class`.`id` = `md`.`vehicle_class_id`
              JOIN `vehicle_class_group` `classGroup` ON `classGroup`.`id` = `class`.`vehicle_class_group_id`
              LEFT JOIN mot_test_emergency_reason AS mter ON mter.id = test.id
                WHERE (`type`.`code` = :normalTestCode OR `type`.`code` = :mysteryShopperTestCode)
                  -- the only tests we take into account are failures or non PRS passed ones
                  AND (`status`.`code` = :failedStatusCode OR (`status`.`code` = :passStatusCode AND `test`.`prs_mot_test_id` IS NULL))
                  AND `test`.`completed_date` IS NOT NULL
                  AND `test`.`completed_date` BETWEEN :startDate AND :endDate
                  AND `mter`.`emergency_log_id` IS NULL";
    }

}
