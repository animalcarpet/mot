<?php

namespace UserApi\Dashboard\Dto;

/**
 * All the tester's stats for the month.
 */
class MonthStats
{
    private $averageTime;
    private $failRate;

    public function __construct(
        $averageTime,
        $failRate
    ) {
        $this->averageTime = $averageTime;
        $this->failRate = $failRate;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'averageTime' => $this->averageTime,
            'failRate' => $this->failRate,
        ];
    }
}
