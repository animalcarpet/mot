<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Repository\AbstractStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterPerformanceResult;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\OrganisationSiteStatusCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TesterStatisticsRepository extends AbstractStatisticsRepository implements AutoWireableInterface
{
    public function getForSite($siteId, $year, $month)
    {
        $this->setDaysConfiguration($year, $month);

        $query = $this->createDefaultNativeQuery($this->getSqlForSite());
        $query->setParameter('siteId', $siteId);

        $scalarResult = $query->getScalarResult();

        /** @var \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterPerformanceResult[] $dbResults */
        $dbResults = [];

        foreach ($scalarResult as $row) {
            $dbResult = new TesterPerformanceResult();
            $this->setTesterPerformanceResult($row, $dbResult);
            $dbResults[] = $dbResult;
        }

        return $dbResults;
    }

    public function getForTester($testerId, $year, $month)
    {
        $this->setDaysConfiguration($year, $month);

        $query = $this->createDefaultNativeQuery($this->getSqlForTester());
        $query->setParameter('testerId', $testerId);

        $scalarResult = $query->getScalarResult();

        /** @var \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\QueryResult\TesterPerformanceResult[] $dbResults */
        $dbResults = [];

        foreach ($scalarResult as $row) {
            $dbResult = new TesterPerformanceResult();
            $this->setTesterPerformanceResult($row, $dbResult);
            $dbResults[] = $dbResult;
        }

        return $dbResults;
    }

    private function setTesterPerformanceResult($row, TesterPerformanceResult $testerPerformanceResult)
    {
        $testerPerformanceResult->setVehicleClassGroup($row['vehicleClassGroup'])
            ->setPersonId($row['person_id'])
            ->setUsername($row['username'])
            ->setTotalTime($row['totalTime'])
            ->setAverageVehicleAgeInMonths((float)$row['averageVehicleAgeInMonths'])
            ->setIsAverageVehicleAgeAvailable(!is_null($row['averageVehicleAgeInMonths']))
            ->setFailedCount($row['failedCount'])
            ->setTotalCount($row ['totalCount']);
        return $testerPerformanceResult;
    }

    private function getSqlForSite()
    {
        return sprintf($this->getSql(), "USE INDEX (`mot_test_site_id_started_date_completed_date_idx`)",  " AND `vts`.`id` = :siteId ");
    }

    private function getSqlForTester()
    {
        return sprintf($this->getSql(), "USE INDEX (`mot_test_person_id_started_date_completed_date_idx`)", " AND `test`.`person_id` = :testerId ");
    }

    private function getSql()
    {
        return "SELECT
                  `class_group`.`code`                                                        `vehicleClassGroup`,
                  `person`.`id`                                                               `person_id`,
                  `person`.`username`                                                         `username`,
                  SUM(TIMESTAMPDIFF(SECOND, `test`.`started_date`, `test`.`completed_date`))  `totalTime`,
                  SUM(IF(`status`.`code` = :failedStatusCode, 1, 0))                          `failedCount`,
                  COUNT(*) `totalCount`,
                  AVG(
                    IF(`v`.`manufacture_date` IS NOT NULL,
                      TIMESTAMPDIFF(MONTH, `v`.`manufacture_date`, `test`.`completed_date`),
                      NULL
                    )
                  ) AS `averageVehicleAgeInMonths`
                FROM `mot_test` `test` %s
                  JOIN `vehicle` `v` ON `test`.`vehicle_id` = `v`.`id`
                  JOIN `person` `person` ON `person`.`id` = `test`.`person_id`
                  JOIN `mot_test_type` `type` ON `type`.`id` = `test`.`mot_test_type_id`
                  JOIN `mot_test_status` `status` ON `status`.`id` = `test`.`status_id`
                  JOIN `vehicle_class` `class` ON `class`.`id` = `test`.`vehicle_class_id`
                  JOIN `vehicle_class_group` `class_group` ON `class_group`.`id` = `class`.`vehicle_class_group_id`
                  JOIN `site` `vts` ON `vts`.`id` = `test`.`site_id`
                WHERE `test`.`completed_date` BETWEEN :startDate AND :endData
                  -- the only tests we take into account are failures or non PRS passed ones
                  AND (`status`.`code` = :failedStatusCode OR (`status`.`code` = :passStatusCode AND `test`.`prs_mot_test_id` IS NULL))
                  AND `type`.`code` = :normalTestCode
                  AND `emergency_log_id` IS NULL
                  %s
                  AND EXISTS
                  (
                  -- the site had to have an active association to the current AE during the time when the test was performed
                      SELECT * FROM `organisation_site_map` `associationToAe`
                      JOIN `organisation_site_status` `associationStatus` ON `associationStatus`.`id` = `associationToAe`.`status_id`
                      WHERE `associationToAe`.`site_id` = `vts`.`id`
                      AND `associationToAe`.`organisation_id` = `vts`.`organisation_id`
                      AND `associationStatus`.`code` NOT IN (:irrelevantAssociationCodes)
                      AND `associationToAe`.`start_date` <= `test`.`completed_date`
                      AND
                      (
                        `associationToAe`.`end_date` >= `test`.`completed_date`
                        OR `associationToAe`.`end_date` IS NULL)
                      )
                GROUP BY `person`.`id`, `class_group`.`code`
                ORDER BY `totalCount` DESC";
    }

    private function createDefaultNativeQuery($sql)
    {
        $rsm = $this
            ->getResultSetMapping()
            ->addScalarResult('vehicleClassGroup', 'vehicleClassGroup')
            ->addScalarResult('person_id', 'person_id')
            ->addScalarResult('username', 'username')
            ->addScalarResult('totalTime', 'totalTime')
            ->addScalarResult('failedCount', 'failedCount')
            ->addScalarResult('totalCount', 'totalCount')
            ->addScalarResult('averageVehicleAgeInMonths', 'averageVehicleAgeInMonths');

        $query = $this->getNativeQuery($sql, $rsm)
            ->setParameter('failedStatusCode', MotTestStatusCode::FAILED)
            ->setParameter('passStatusCode', MotTestStatusCode::PASSED)
            ->setParameter('normalTestCode', MotTestTypeCode::NORMAL_TEST)
            ->setParameter('startDate', $this->startDate)
            ->setParameter('endData', $this->endDate)
            ->setParameter('irrelevantAssociationCodes',
                [
                    OrganisationSiteStatusCode::APPLIED,
                    OrganisationSiteStatusCode::UNKNOWN
                ]
            );

        return $query;
    }
}
