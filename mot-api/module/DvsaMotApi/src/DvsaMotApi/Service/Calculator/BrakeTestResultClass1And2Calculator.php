<?php

namespace DvsaMotApi\Service\Calculator;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaEntities\Entity\BrakeTestResultClass12;

/**
 * Class BrakeTestResultClass1And2Calculator.
 */
class BrakeTestResultClass1And2Calculator extends BrakeTestResultCalculatorBase
{
    const CONTROL_PRIMARY_MINIMUM_EFFICIENCY = 30;
    const CONTROL_SECONDARY_MINIMUM_EFFICIENCY = 25;
    const LOCKS_MINIMUM_PERCENT_FOR_PASS = 50;

    public function calculateBrakeTestResult(BrakeTestResultClass12 $brakeTestResult, \DateTime $firstUsedDate)
    {
        switch ($brakeTestResult->getBrakeTestType()->getCode()) {
            case BrakeTestTypeCode::ROLLER:
            case BrakeTestTypeCode::FLOOR:
            case BrakeTestTypeCode::PLATE:
                $brakeTestResult->setControl1BrakeEfficiency($this->calculateControl1Efficiency($brakeTestResult));
                $brakeTestResult->setControl2BrakeEfficiency($this->calculateControl2Efficiency($brakeTestResult));
                $this->performEfficiencyPassCalculations($brakeTestResult, $firstUsedDate);
                break;
            case BrakeTestTypeCode::DECELEROMETER:
                $this->performEfficiencyPassCalculations($brakeTestResult, $firstUsedDate);
                break;
            case BrakeTestTypeCode::GRADIENT:
                $this->performGradientPassCalculations($brakeTestResult, $firstUsedDate);
                break;
        }

        return $brakeTestResult;
    }

    protected function performEfficiencyPassCalculations(
        BrakeTestResultClass12 $brakeTestResult,
        \DateTime $firstUsedDate
    ) {
        $brakeTestResult->setControl1EfficiencyPass($this->isPassingControl1Efficiency($brakeTestResult));
        $brakeTestResult->setControl2EfficiencyPass(
            $this->isPassingControl2Efficiency($brakeTestResult, $firstUsedDate)
        );
        $brakeTestResult->setGeneralPass($this->isPassing($brakeTestResult, $firstUsedDate));
    }

    protected function performGradientPassCalculations(
        BrakeTestResultClass12 $brakeTestResult,
        \DateTime $firstUsedDate
    ) {
        $brakeTestResult->setControl1EfficiencyPass(
            $this->isPassingGradientEfficiency(
                $brakeTestResult->getGradientControl1AboveUpperMinimum(),
                $brakeTestResult->getGradientControl1BelowMinimum(),
                $brakeTestResult->getGradientControl2AboveUpperMinimum()
            )
        );
        $brakeTestResult->setControl2EfficiencyPass(
            $this->isPassingGradientEfficiency(
                $brakeTestResult->getGradientControl2AboveUpperMinimum(),
                $brakeTestResult->getGradientControl2BelowMinimum(),
                $brakeTestResult->getGradientControl1AboveUpperMinimum()
            )
        );
        $brakeTestResult->setGeneralPass($this->isPassing($brakeTestResult, $firstUsedDate));
    }

    public function calculateControl1PercentLocked(BrakeTestResultClass12 $brakeTestResult)
    {
        $locks = [];
        $locks[] = $brakeTestResult->getControl1LockFront();
        $brakeEfforts = [];
        $brakeEfforts[] = $brakeTestResult->getControl1EffortFront();
        /* @BL-412 For FLOOR type test, we already only use values for front */
        if ($brakeTestResult->getBrakeTestType()->getCode() !== BrakeTestTypeCode::FLOOR) {
            $locks[] = $brakeTestResult->getControl1LockRear();
            $brakeEfforts[] = $brakeTestResult->getControl1EffortRear();
        }

        return $this->calculatePercentLockedClass1And2($locks, $brakeEfforts);
    }

    public function calculateControl2PercentLocked(BrakeTestResultClass12 $brakeTestResult)
    {
        $locks = [];
        $locks[] = $brakeTestResult->getControl2LockFront();
        $brakeEfforts = [];
        $brakeEfforts[] = $brakeTestResult->getControl2EffortFront();
        /* @BL-412 For FLOOR type test, we already only use values for front */
        if ($brakeTestResult->getBrakeTestType()->getCode() !== BrakeTestTypeCode::FLOOR) {
            $locks[] = $brakeTestResult->getControl2LockRear();
            $brakeEfforts[] = $brakeTestResult->getControl2EffortRear();
        }

        return $this->calculatePercentLockedClass1And2($locks, $brakeEfforts);
    }

    public function areBothControlsUnderSecondaryMinimum(BrakeTestResultClass12 $brakeTestResult)
    {
        if ($brakeTestResult->getBrakeTestType()->getCode() === BrakeTestTypeCode::GRADIENT) {
            return $brakeTestResult->getControl1EfficiencyPass() === false
            && $brakeTestResult->getControl2EfficiencyPass() === false
            && $brakeTestResult->getGradientControl1BelowMinimum() === true
            && $brakeTestResult->getGradientControl2BelowMinimum() === true;
        } else {
            return $brakeTestResult->getControl1EfficiencyPass() === false
            && $brakeTestResult->getControl2EfficiencyPass() === false
            && $brakeTestResult->getControl1BrakeEfficiency() < self::CONTROL_SECONDARY_MINIMUM_EFFICIENCY
            && $brakeTestResult->getControl2BrakeEfficiency() < self::CONTROL_SECONDARY_MINIMUM_EFFICIENCY;
        }
    }

    public function noControlReachesPrimaryMinimum(BrakeTestResultClass12 $brakeTestResult)
    {
        return $brakeTestResult->getControl1EfficiencyPass() === false
        && $brakeTestResult->getControl2EfficiencyPass() === false;
    }

    public function oneControlNotReachingSecondaryMinimum(
        BrakeTestResultClass12 $brakeTestResult
    ) {
        return $brakeTestResult->getControl1EfficiencyPass() !== false
            xor $brakeTestResult->getControl2EfficiencyPass() !== false;
    }

    protected function calculateControl1Efficiency(
        BrakeTestResultClass12 $brakeTestResult
    ) {
        $front = $brakeTestResult->getControl1EffortFront();
        $rear = $brakeTestResult->getControl1EffortRear();
        $sidecar = $brakeTestResult->getControl1EffortSidecar();
        $effort = intval($front) + intval($rear) + intval($sidecar);

        return $this->calculateEfficiency($effort, $this->getWeight($brakeTestResult));
    }

    protected function calculateControl2Efficiency(
        BrakeTestResultClass12 $brakeTestResult
    ) {
        $front = $brakeTestResult->getControl2EffortFront();
        $rear = $brakeTestResult->getControl2EffortRear();
        $sidecar = $brakeTestResult->getControl2EffortSidecar();
        if ($front === null && $rear === null && $sidecar === null) {
            return;
        }
        $effort = intval($front) + intval($rear) + intval($sidecar);

        return $this->calculateEfficiency($effort, $this->getWeight($brakeTestResult));
    }

    protected function getWeight(
        BrakeTestResultClass12 $brakeTestResult
    ) {
        return $brakeTestResult->getRiderWeight()
        + $brakeTestResult->getSidecarWeight()
        + $brakeTestResult->getVehicleWeightFront()
        + $brakeTestResult->getVehicleWeightRear();
    }

    protected function isPassingControl1Efficiency(
        BrakeTestResultClass12 $brakeTestResult
    ) {
        return $this->isPassingEfficiency(
            $brakeTestResult->getBrakeTestType()->getCode(),
            $brakeTestResult->getControl1BrakeEfficiency(),
            $brakeTestResult->getControl2BrakeEfficiency(),
            $this->calculateControl1PercentLocked($brakeTestResult),
            $this->calculateControl2PercentLocked($brakeTestResult)
        );
    }

    protected function isPassingControl2Efficiency(
        BrakeTestResultClass12 $brakeTestResult,
        \DateTime $firstUsed
    ) {
        $control2BrakeEfficiency = $brakeTestResult->getControl2BrakeEfficiency();
        if ($control2BrakeEfficiency === null) {
            if ($this->oneControlAllowedInBike($firstUsed)) {
                return;
            } else {
                return false;
            }
        }

        return $this->isPassingEfficiency(
            $brakeTestResult->getBrakeTestType()->getCode(),
            $brakeTestResult->getControl2BrakeEfficiency(),
            $brakeTestResult->getControl1BrakeEfficiency(),
            $this->calculateControl2PercentLocked($brakeTestResult),
            $this->calculateControl1PercentLocked($brakeTestResult)
        );
    }

    protected function isPassingEfficiency(
        $testType,
        $currentControlEfficiency,
        $otherControlEfficiency,
        $percentLockedCurrentControl,
        $percentLockedOtherControl
    ) {
        return
            $currentControlEfficiency >= self::CONTROL_PRIMARY_MINIMUM_EFFICIENCY
            || $percentLockedCurrentControl >= self::LOCKS_MINIMUM_PERCENT_FOR_PASS
            || ($currentControlEfficiency >= self::CONTROL_SECONDARY_MINIMUM_EFFICIENCY
                && ($otherControlEfficiency >= self::CONTROL_PRIMARY_MINIMUM_EFFICIENCY
                || $percentLockedOtherControl >= self::LOCKS_MINIMUM_PERCENT_FOR_PASS));
    }

    protected function isPassingGradientEfficiency(
        $controlAbovePrimary,
        $controlBelowSecondary,
        $otherControlAbovePrimary
    ) {
        return $controlAbovePrimary || (!$controlBelowSecondary && $otherControlAbovePrimary);
    }

    protected function isPassing(
        BrakeTestResultClass12 $brakeTestResult,
        \DateTime $firstUsedDate
    ) {
        $pass1 = $brakeTestResult->getControl1EfficiencyPass();
        $pass2 = $brakeTestResult->getControl2EfficiencyPass();

        return $pass1 === true
        && ($pass2 === true || ($pass2 === null && $this->oneControlAllowedInBike($firstUsedDate)));
    }

    protected function oneControlAllowedInBike(
        $firstUsedDate
    ) {
        $limitDate = new \DateTime(BrakeTestResultClass12::DATE_FIRST_USED_ONLY_ONE_CONTROL_ALLOWED);

        return $firstUsedDate < $limitDate;
    }
}
