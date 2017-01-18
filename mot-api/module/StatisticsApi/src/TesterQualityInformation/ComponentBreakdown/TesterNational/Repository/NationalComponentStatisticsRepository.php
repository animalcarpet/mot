<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\Repository\ComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\QueryBuilder\NationalComponentBreakdownQueryBuilder;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class NationalComponentStatisticsRepository extends ComponentStatisticsRepository implements AutoWireableInterface
{
    public function get($group, $year, $month)
    {
        $qb = new NationalComponentBreakdownQueryBuilder();

        $this->setDaysConfiguration($year, $month);

        return $this->getResult($qb->getSql(), [
            ComponentStatisticsRepository::PARAM_GROUP      => $group,
            ComponentStatisticsRepository::PARAM_START_DATE => $this->startDate,
            ComponentStatisticsRepository::PARAM_END_DATE   => $this->endDate,
        ]);
    }

    public function getNationalFailedMotTestCount($group, $year, $month)
    {
        $this->setDaysConfiguration($year, $month);

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('testCount', 'testCount');

        $sql = "SELECT COUNT(*) AS `testCount`
            FROM `mot_test_current` `mt`
              USE INDEX (`ix_mot_test_current_completed_date`)
              LEFT JOIN `vehicle` ON (`vehicle`.`id` = `mt`.`vehicle_id`) AND (`vehicle`.`version` = `mt`.`vehicle_version`)
              LEFT JOIN `vehicle_hist` ON (`vehicle_hist`.`id` = `mt`.`vehicle_id`) AND (`vehicle_hist`.`version` = `mt`.`vehicle_version`)
              JOIN `model_detail` `md` ON `md`.`id` = COALESCE (`vehicle`.`model_detail_id`, `vehicle_hist`.`model_detail_id`)
              JOIN `vehicle_class` `vc` ON `vc`.`id` = `md`.`vehicle_class_id`
              JOIN `vehicle_class_group` `vcg` ON `vc`.`vehicle_class_group_id` = `vcg`.`id`
              JOIN `mot_test_status` `mts` ON `mt`.`status_id` = `mts`.`id`
            WHERE `vcg`.`code` = :groupCode
              AND `mts`.`code` = :failedStatusCode
              AND `mt`.`completed_date` BETWEEN :startDate AND :endDate";

        $query = $this->entityManager->createNativeQuery($sql, $rsm);

        $query->setParameter('groupCode', $group);
        $query->setParameter('failedStatusCode', MotTestStatusCode::FAILED);
        $query->setParameter('startDate', $this->startDate);
        $query->setParameter('endDate', $this->endDate);

        return $query->getResult()[0]['testCount'];
    }
}
