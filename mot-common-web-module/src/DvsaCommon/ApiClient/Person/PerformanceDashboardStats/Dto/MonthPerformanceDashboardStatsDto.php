<?php

namespace DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto;

use DvsaCommon\Date\TimeSpan;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;
/**
 * All the tester's stats for the month.
 */
class MonthPerformanceDashboardStatsDto implements ReflectiveDtoInterface
{
    private $totalTestsCount;
    private $passedTestsCount;
    private $failedTestsCount;
    private $averageTime;
    private $failRate;

    /**
     * @return int
     */
    public function getTotalTestsCount()
    {
        return $this->totalTestsCount;
    }

    /**
     * @param int $totalTestsCount
     * @return $this
     */
    public function setTotalTestsCount($totalTestsCount)
    {
        $this->totalTestsCount = $totalTestsCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getPassedTestsCount()
    {
        return $this->passedTestsCount;
    }

    /**
     * @param int $passedTestsCount
     * @return $this
     */
    public function setPassedTestsCount($passedTestsCount)
    {
        $this->passedTestsCount = $passedTestsCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getFailedTestsCount()
    {
        return $this->failedTestsCount;
    }

    /**
     * @param int $failedTestsCount
     * @return $this
     */
    public function setFailedTestsCount($failedTestsCount)
    {
        $this->failedTestsCount = $failedTestsCount;
        return $this;
    }

    /**
     * @return TimeSpan
     */
    public function getAverageTime()
    {
        return $this->averageTime;
    }

    public function setAverageTime(TimeSpan $averageTime)
    {
        $this->averageTime = $averageTime;
        return $this;
    }

    /**
     * @return float
     */
    public function getFailRate()
    {
        return $this->failRate;
    }

    /**
     * @param float $failRate
     * @return $this
     */
    public function setFailRate($failRate)
    {
        $this->failRate = $failRate;
        return $this;
    }
}
