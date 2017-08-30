<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use DvsaCommon\Date\LastMonthsDateRange;

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

    /**
     * @param LastMonthsDateRange $monthsRangeDate
     */
    protected function setMonthsRangeConfiguration(LastMonthsDateRange $monthsRangeDate)
    {
        $this->startDate = $monthsRangeDate->getStartDate();
        $this->endDate = $monthsRangeDate->getEndDate();
    }
}
