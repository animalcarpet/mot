<?php

namespace UserApi\Dashboard\Dto;

/**
 * All the tester's stats for the day.
 */
class DayStats
{
    private $total;
    private $numberOfPasses;
    private $numberOfFails;

    public function __construct(
        $total,
        $numberOfPasses,
        $numberOfFails
    ) {
        $this->total = $total;
        $this->numberOfPasses = $numberOfPasses;
        $this->numberOfFails = $numberOfFails;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'total' => $this->total,
            'numberOfPasses' => $this->numberOfPasses,
            'numberOfFails' => $this->numberOfFails,
        ];
    }
}
