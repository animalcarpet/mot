<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Common\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Constants\MotConfig\GqrStatsDateRangeConfigKeys;
use DvsaCommon\Constants\MotConfig\MotConfigKeys;
use DvsaCommon\Date\DateRangeInterface;

class AbstractStatisticsRepository
{
    protected $entityManager;
    protected $lastDay;
    protected $startDate;
    protected $endDate;
    protected $motConfig;

    public function __construct(EntityManager $entityManager, MotConfig $motConfig)
    {
        $this->entityManager = $entityManager;
        $this->motConfig = $motConfig;
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
     * @param DateRangeInterface $monthsRangeDate
     */
    protected function setMonthsRangeConfiguration(DateRangeInterface $monthsRangeDate)
    {
        $this->startDate = $monthsRangeDate->getStartDate();

        $endDate = $monthsRangeDate->getEndDate();

        $gqrMinDate = $this->motConfig->get(MotConfigKeys::GQR_STATS_DATE_RANGE, GqrStatsDateRangeConfigKeys::MIN_DATE);
        $gqrMaxDate = $this->motConfig->get(MotConfigKeys::GQR_STATS_DATE_RANGE, GqrStatsDateRangeConfigKeys::MAX_DATE);
        $gqrEndDate = $this->motConfig->get(MotConfigKeys::GQR_STATS_DATE_RANGE, GqrStatsDateRangeConfigKeys::END_DATE);

        if ($endDate >= new \DateTime($gqrMinDate) && $endDate <= new \DateTime($gqrMaxDate)) {
            $endDate = new \DateTime($gqrEndDate);
        }

        $this->endDate = $endDate;
    }
}
