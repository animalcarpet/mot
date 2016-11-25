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
                  FROM `mot_test` `test` USE INDEX (`mot_test_completed_date_idx`)
                  JOIN `mot_test_status` `status` ON `status`.`id` = `test`.`status_id`
                  JOIN `mot_test_type` `type` ON `type`.`id` = `test`.`mot_test_type_id`
                  JOIN `vehicle_class` `class` ON `class`.`id` = `test`.`vehicle_class_id`
                  JOIN `vehicle_class_group` `classGroup` ON `classGroup`.`id` = `class`.`vehicle_class_group_id`
                  WHERE `test`.`completed_date` BETWEEN :startDate AND :endDate
                  AND (`type`.`code` = :normalTestCode OR `type`.`code` = :mysteryShopperTestCode)
                  -- the only tests we take into account are failures or non PRS passed ones
                  AND (`status`.`code` = :failedStatusCode OR (`status`.`code` = :passStatusCode AND `test`.`prs_mot_test_id` IS NULL))
                AND `test`.`emergency_log_id` IS NULL
                AND `classGroup`.`code` = :classGroupCode";
    }

    private function getSqlForStatistics()
    {
        return "SELECT
              sum(IF(`classGroup`.`code` = :groupACode, 1, 0))                                        `totalACount`,
              sum(IF(`classGroup`.`code` = :groupACode AND `status`.`code` = :failedStatusCode, 1, 0)) `totalAFailCount`,
              sum(IF(`classGroup`.`code` = :groupACode, TIMESTAMPDIFF(SECOND , `test`.`started_date`, `test`.`completed_date`), 0)) `totalATestTime`,
              AVG(
                  -- count average age only for vehicles with manufacture date set
                  IF(`classGroup`.`code` = 'A', IF(`v`.`manufacture_date` IS NOT NULL,
                     TIMESTAMPDIFF(MONTH, `v`.`manufacture_date`, `test`.`completed_date`),
                     NULL
                  ), NULL)
              ) AS `totalAVehicleAgeInMonths`,
              sum(IF(`classGroup`.`code` = :groupBCode, 1, 0))                                        `totalBCount`,
              sum(IF(`classGroup`.`code` = :groupBCode AND `status`.`code` = :failedStatusCode, 1, 0)) `totalBFailCount`,
              sum(IF(`classGroup`.`code` = :groupBCode, TIMESTAMPDIFF(SECOND , `test`.`started_date`, `test`.`completed_date`), 0)) `totalBTestTime`,
              AVG(
                  IF(`classGroup`.`code` = 'B', IF(`v`.`manufacture_date` IS NOT NULL,
                     TIMESTAMPDIFF(MONTH, `v`.`manufacture_date`, `test`.`completed_date`),
                     NULL
                  ), NULL)
              ) AS `totalBVehicleAgeInMonths`
            FROM `mot_test` `test` USE INDEX (`mot_test_completed_date_idx`)
              JOIN `vehicle` `v` ON `test`.`vehicle_id` = `v`.`id`
              JOIN `mot_test_status` `status` ON `status`.`id` = `test`.`status_id`
              JOIN `mot_test_type` `type` ON `type`.`id` = `test`.`mot_test_type_id`
              JOIN `vehicle_class` `class` ON `class`.`id` = `test`.`vehicle_class_id`
              JOIN `vehicle_class_group` `classGroup` ON `classGroup`.`id` = `class`.`vehicle_class_group_id`
                WHERE (`type`.`code` = :normalTestCode OR `type`.`code` = :mysteryShopperTestCode)
                  -- the only tests we take into account are failures or non PRS passed ones
                  AND (`status`.`code` = :failedStatusCode OR (`status`.`code` = :passStatusCode AND `test`.`prs_mot_test_id` IS NULL))
                  AND `test`.`completed_date` IS NOT NULL
                  AND `test`.`completed_date` BETWEEN :startDate AND :endDate
                  AND `test`.`emergency_log_id` IS NULL";
    }

}
