<?php

namespace DvsaMotApi\Service\Calculator;

/**
 * Class BrakeTestResultCalculatorBase.
 */
abstract class BrakeTestResultCalculatorBase
{
    /**
     * @param array $locks
     * @param array $brakeEfforts
     *
     * @return float|int
     */
    protected function calculatePercentLockedClass1And2(array $locks, array $brakeEfforts)
    {
        $numberOfBrakeEfforts = 0;
        foreach ($brakeEfforts as $brakeEffort) {
            if ($brakeEffort !== null) {
                $numberOfBrakeEfforts += 1;
            }
        }
        if ($numberOfBrakeEfforts === 0) {
            return 0;
        }
        $numberOfLocks = $this->numberOfLocks($locks);
        $result = round(100 * $numberOfLocks / $numberOfBrakeEfforts);
        $result = min($result, 100);
        $result = max($result, 0);

        return $result;
    }

    /**
     * @param array $locks
     *
     * @return float|int
     */
    protected function calculatePercentLockedClass3AndAbove(array $locks)
    {
        $locksCount = count($locks);
        if ($locksCount === 0) {
            return 0;
        }
        $numberOfLocks = $this->numberOfLocks($locks);

        return round(100 * $numberOfLocks / $locksCount);
    }

    /**
     * @param $effort
     * @param $weight
     *
     * @return float|int
     */
    protected function calculateEfficiency($effort, $weight)
    {
        if (!$weight) {
            return 0;
        }

        $result = $effort / $weight;
        $result *= 100;
        $result = round($result, 3);

        return floor($result);
    }

    /**
     * @param array $locks
     *
     * @return int
     */
    private function numberOfLocks(array $locks)
    {
        $numberOfLocks = 0;
        foreach ($locks as $lock) {
            if ($lock === true) {
                $numberOfLocks += 1;
            }
        }

        return $numberOfLocks;
    }
}
