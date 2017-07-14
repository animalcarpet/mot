<?php

namespace DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;
/**
 * All the tester's stats for the day.
 */
class DayPerformanceDashboardStatsDto implements ReflectiveDtoInterface
{
    private $total;
    private $numberOfPasses;
    private $numberOfFails;

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param int $total
     * @return $this
     */
    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfPasses()
    {
        return $this->numberOfPasses;
    }

    /**
     * @param int $numberOfPasses
     * @return $this
     */
    public function setNumberOfPasses($numberOfPasses)
    {
        $this->numberOfPasses = $numberOfPasses;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfFails()
    {
        return $this->numberOfFails;
    }

    /**
     * @param int $numberOfFails
     * @return $this
     */
    public function setNumberOfFails($numberOfFails)
    {
        $this->numberOfFails = $numberOfFails;
        return $this;
    }
}
