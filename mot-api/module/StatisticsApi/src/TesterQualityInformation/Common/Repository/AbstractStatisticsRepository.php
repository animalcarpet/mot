<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;

class AbstractStatisticsRepository
{
    protected $entityManager;
    protected $lastDay;
    protected $startDate;
    protected $endDate;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $sql
     * @param $rsm
     *
     * @return \Doctrine\ORM\NativeQuery
     */
    protected function getNativeQuery($sql, $rsm)
    {
        return $this->entityManager->createNativeQuery($sql, $rsm);
    }

    /**
     * @return ResultSetMapping
     */
    protected function getResultSetMapping()
    {
        return new ResultSetMapping();
    }

    protected function setDaysConfiguration($year, $month)
    {
        $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $this->startDate = sprintf('%s-%s-1 00:00:00', $year, $month);
        $this->endDate = sprintf('%s-%s-%s 23:59:59', $year, $month, $lastDay);
    }
}
