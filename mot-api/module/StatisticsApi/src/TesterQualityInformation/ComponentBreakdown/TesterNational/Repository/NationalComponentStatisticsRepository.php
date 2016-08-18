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
            FROM `mot_test` `mt`
              USE INDEX (`mot_test_completed_date_idx`)
              JOIN `vehicle_class` `vc` ON `mt`.`vehicle_class_id` = `vc`.`id`
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
