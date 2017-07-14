<?php

namespace DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;
/**
 * All the tester's stats for the month.
 */
class PerformanceDashboardStatsDto implements ReflectiveDtoInterface
{
    private $monthStats;
    private $dayStats;

    /**
     * @return DayPerformanceDashboardStatsDto
     */
    public function getDayStats()
    {
        return $this->dayStats;
    }

    public function setDayStats(DayPerformanceDashboardStatsDto $dayStats)
    {
        $this->dayStats = $dayStats;
        return $this;
    }

    /**
     * @return MonthPerformanceDashboardStatsDto
     */
    public function getMonthStats()
    {
        return $this->monthStats;
    }

    public function setMonthStats(MonthPerformanceDashboardStatsDto $monthStats)
    {
        $this->monthStats = $monthStats;
        return $this;
    }
}