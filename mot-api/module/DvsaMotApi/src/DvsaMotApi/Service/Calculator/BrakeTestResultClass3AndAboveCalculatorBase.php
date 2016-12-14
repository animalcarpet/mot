<?php

namespace DvsaMotApi\Service\Calculator;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\BrakeTestResultServiceBrakeData;
use DvsaEntities\Entity\Vehicle;

/**
 * Class BrakeTestResultClass3AndAboveCalculatorBase.
 */
abstract class BrakeTestResultClass3AndAboveCalculatorBase extends BrakeTestResultCalculatorBase
{
    const LOCKS_MINIMUM_EFFICIENCY = -1;
    const WHEEL_COUNT_3 = 3;
    const WHEEL_COUNT_4 = 4;
    const WHEEL_COUNT_6 = 6;
    const SERVICE_BRAKE_1 = 1;
    const SERVICE_BRAKE_2 = 2;
    const IMBALANCE_MAXIMUM = 30;

    public function calculateBrakeTestResult(BrakeTestResultClass3AndAbove $brakeTestResult, Vehicle $vehicle)
    {
        $this->resetCalculatedFields($brakeTestResult);

        $vehicleClass = $vehicle->getVehicleClass()->getCode();
        $vehicleWeight = $brakeTestResult->getVehicleWeight();
        $serviceBrake1Data = $brakeTestResult->getServiceBrake1Data();

        //service brake calculations
        if ($serviceBrake1Data
            && $this->isTestTypeWithEffortCalculations(
                $brakeTestResult->getServiceBrake1TestType()->getCode()
            )
        ) {
            // for decelerometer efficiency is already set

            $this->resetCalculatedFieldsForServiceBrake($serviceBrake1Data);
            $brakeTestResult->setServiceBrake1Efficiency(
                $this->calculateServiceBrakeEfficiency(
                    $serviceBrake1Data,
                    $vehicleWeight,
                    $this->getWheelCount($brakeTestResult, $vehicle),
                    $brakeTestResult->getIsSingleInFront()
                )
            );

            $this->performImbalanceCalculations(
                $serviceBrake1Data,
                $this->getWheelCount($brakeTestResult, $vehicle),
                $brakeTestResult->getIsSingleInFront()
            );
        }

        if ($brakeTestResult->getServiceBrake2TestType() !== null) {
            $serviceBrake2Data = $brakeTestResult->getServiceBrake2Data();
            if ($serviceBrake2Data
                && $this->isTestTypeWithEffortCalculations(
                    $brakeTestResult->getServiceBrake2TestType()->getCode()
                )
            ) {
                // for decelerometer efficiency is already set

                $this->resetCalculatedFieldsForServiceBrake($serviceBrake2Data);
                $brakeTestResult->setServiceBrake2Efficiency(
                    $this->calculateServiceBrakeEfficiency(
                        $serviceBrake2Data,
                        $vehicleWeight,
                        $this->getWheelCount($brakeTestResult, $vehicle),
                        $brakeTestResult->getIsSingleInFront()
                    )
                );
                $this->performImbalanceCalculations(
                    $serviceBrake2Data,
                    $this->getWheelCount($brakeTestResult, $vehicle),
                    $brakeTestResult->getIsSingleInFront()
                );
            }
        }

        //service brake passes
        $serviceBrake1EfficiencyPass = $brakeTestResult->getServiceBrake1EfficiencyPass();
        if (!in_array(
            $brakeTestResult->getServiceBrake1TestType()->getCode(),
            [BrakeTestTypeCode::GRADIENT]
        )
        ) {
            $serviceBrake1EfficiencyPass = $this->isPassingServiceBrakeEfficiency(
                $vehicle,
                $brakeTestResult->getServiceBrake1TestType()->getCode(),
                $brakeTestResult,
                self::SERVICE_BRAKE_1
            );
        }
        $brakeTestResult->setServiceBrake1EfficiencyPass($serviceBrake1EfficiencyPass);

        if ($brakeTestResult->getServiceBrake2TestType() !== null) {
            $serviceBrake2EfficiencyPass = $brakeTestResult->getServiceBrake2EfficiencyPass();
            if (!in_array(
                $brakeTestResult->getServiceBrake2TestType()->getCode(),
                [BrakeTestTypeCode::GRADIENT]
            )
            ) {
                $serviceBrake2EfficiencyPass = $this->isPassingServiceBrakeEfficiency(
                    $vehicle,
                    $brakeTestResult->getServiceBrake2TestType()->getCode(),
                    $brakeTestResult,
                    self::SERVICE_BRAKE_2
                );
            }
            $brakeTestResult->setServiceBrake2EfficiencyPass($serviceBrake2EfficiencyPass);
        }

        switch ($brakeTestResult->getParkingBrakeTestType()->getCode()) {
            case BrakeTestTypeCode::GRADIENT:
                // efficiency pass is already set!
                break;
            case BrakeTestTypeCode::PLATE:
            case BrakeTestTypeCode::ROLLER:
                $brakeTestResult->setParkingBrakeEfficiency($this->calculateParkingBrakeEfficiency($brakeTestResult));
                $brakeTestResult->setParkingBrakeEfficiencyPass(
                    $this->isPassingParkingBrakeEfficiency($brakeTestResult, $vehicleClass)
                );
                //check if parking brake imbalance applicable
                if ($brakeTestResult->getServiceBrakeIsSingleLine()) {
                    $brakeTestResult->setParkingBrakeImbalance($this->calculateParkingBrakeImbalance($brakeTestResult));
                    //check if parking brake secondary axle is applicable in this case
                    if ($brakeTestResult->getParkingBrakeEffortSecondaryNearside() !== null
                        || $brakeTestResult->getParkingBrakeEffortSecondaryOffside() !== null
                    ) {
                        $brakeTestResult->setParkingBrakeSecondaryImbalance(
                            $this->calculateParkingBrakeSecondaryImbalance($brakeTestResult)
                        );
                    }
                    $brakeTestResult->setParkingBrakeImbalancePass(
                        $this->isPassingParkingBrakeImbalance($brakeTestResult, $vehicleClass)
                    );
                }
                break;
            case BrakeTestTypeCode::DECELEROMETER:
                $brakeTestResult->setParkingBrakeEfficiencyPass(
                    $this->isPassingParkingBrakeEfficiency($brakeTestResult, $vehicleClass)
                );
        }

        $brakeTestResult->setGeneralPass($this->isPassing($brakeTestResult));

        return $brakeTestResult;
    }

    public function calculateServiceBrakePercentLocked(
        BrakeTestResultServiceBrakeData $serviceBrake,
        BrakeTestResultClass3AndAbove $brakeTestResult
    ) {
        $potentialLocks = [];
        if ($serviceBrake->getEffortNearsideAxle1() !== null
            || $serviceBrake->getEffortOffsideAxle1() !== null
        ) {
            $potentialLocks[] = $serviceBrake->getLockNearsideAxle1()
                && $serviceBrake->getEffortNearsideAxle1() >= self::LOCKS_MINIMUM_EFFICIENCY;
            $potentialLocks[] = $serviceBrake->getLockOffsideAxle1()
                && $serviceBrake->getEffortOffsideAxle1() >= self::LOCKS_MINIMUM_EFFICIENCY;
        }
        if ($brakeTestResult->getIsSingleInFront() !== null) {
            if ($serviceBrake->getEffortSingle() !== null) {
                $potentialLocks[] = $serviceBrake->getLockSingle()
                    && $serviceBrake->getEffortSingle() >= self::LOCKS_MINIMUM_EFFICIENCY;
            }
        }
        if ($serviceBrake->getEffortNearsideAxle2() !== null
            || $serviceBrake->getEffortOffsideAxle2() !== null
        ) {
            $potentialLocks[] = $serviceBrake->getLockNearsideAxle2()
                && $serviceBrake->getEffortNearsideAxle2() >= self::LOCKS_MINIMUM_EFFICIENCY;
            $potentialLocks[] = $serviceBrake->getLockOffsideAxle2()
                && $serviceBrake->getEffortOffsideAxle2() >= self::LOCKS_MINIMUM_EFFICIENCY;
        }
        if ($serviceBrake->getEffortNearsideAxle3() !== null
            || $serviceBrake->getEffortOffsideAxle3() !== null
        ) {
            $potentialLocks[] = $serviceBrake->getLockNearsideAxle3()
                && $serviceBrake->getEffortNearsideAxle3() >= self::LOCKS_MINIMUM_EFFICIENCY;
            $potentialLocks[] = $serviceBrake->getLockOffsideAxle3()
                && $serviceBrake->getEffortOffsideAxle3() >= self::LOCKS_MINIMUM_EFFICIENCY;
        }

        return $this->calculatePercentLockedClass3AndAbove($potentialLocks);
    }

    public function calculateParkingBrakePercentLocked(BrakeTestResultClass3AndAbove $brakeTestResult)
    {
        $potentialLocks = [];
        if ($brakeTestResult->getParkingBrakeLockSingle() !== null) {
            $potentialLocks[] = $brakeTestResult->getParkingBrakeLockSingle()
                && $brakeTestResult->getParkingBrakeEffortSingle() >= self::LOCKS_MINIMUM_EFFICIENCY;
        } else {
            $potentialLocks[] = $brakeTestResult->getParkingBrakeLockOffside()
                && $brakeTestResult->getParkingBrakeEffortOffside() >= self::LOCKS_MINIMUM_EFFICIENCY;
            $potentialLocks[] = $brakeTestResult->getParkingBrakeLockNearside()
                && $brakeTestResult->getParkingBrakeEffortNearside() >= self::LOCKS_MINIMUM_EFFICIENCY;
            if ($brakeTestResult->getParkingBrakeEffortSecondaryNearside() !== null
                || $brakeTestResult->getParkingBrakeEffortSecondaryOffside() !== null
            ) {
                $potentialLocks[] = $brakeTestResult->getParkingBrakeLockSecondaryOffside()
                    && $brakeTestResult->getParkingBrakeEffortSecondaryOffside() >= self::LOCKS_MINIMUM_EFFICIENCY;
                $potentialLocks[] = $brakeTestResult->getParkingBrakeLockSecondaryNearside()
                    && $brakeTestResult->getParkingBrakeEffortSecondaryNearside() >= self::LOCKS_MINIMUM_EFFICIENCY;
            }
        }

        return $this->calculatePercentLockedClass3AndAbove($potentialLocks);
    }

    protected function isTestTypeWithEffortCalculations($testType)
    {
        return $testType === BrakeTestTypeCode::ROLLER || $testType === BrakeTestTypeCode::PLATE;
    }

    /**
     * Sets imbalance properties in passed serviceBrakeData.
     *
     * @param BrakeTestResultServiceBrakeData $serviceBrakeData
     * @param int                             $wheelCount
     * @param bool|null                       $isSingleInFront
     */
    protected function performImbalanceCalculations(
        BrakeTestResultServiceBrakeData &$serviceBrakeData,
        $wheelCount,
        $isSingleInFront
    ) {
        $imbalancePassing = true;
        $shouldCalcImbalanceForAxle1 = !($wheelCount === self::WHEEL_COUNT_3 && $isSingleInFront === true);
        $shouldCalcImbalanceForAxle2 = !($wheelCount === self::WHEEL_COUNT_3 && $isSingleInFront === false);
        $shouldCalcImbalanceForAxle3 = $wheelCount === self::WHEEL_COUNT_6;

        if ($shouldCalcImbalanceForAxle1) {
            $serviceBrakeData->setImbalanceAxle1($this->calculateServiceBrakeImbalanceAxle1($serviceBrakeData));
            $axle1ImbalanceWithinLimits = $serviceBrakeData->getImbalanceAxle1() <= self::IMBALANCE_MAXIMUM;
            $axle1ImbalanceOK = $axle1ImbalanceWithinLimits
                || (!$axle1ImbalanceWithinLimits
                    && $this->isWheelWithLowerEfficiencyLocked(
                        $serviceBrakeData->getEffortOffsideAxle1(),
                        $serviceBrakeData->getLockOffsideAxle1(),
                        $serviceBrakeData->getEffortNearsideAxle1(),
                        $serviceBrakeData->getLockNearsideAxle1()
                    ));
            $serviceBrakeData->setImbalancePassForAxle(1, $axle1ImbalanceOK);
            $imbalancePassing = $imbalancePassing && $axle1ImbalanceOK;
        }

        if ($shouldCalcImbalanceForAxle2) {
            $serviceBrakeData->setImbalanceAxle2($this->calculateServiceBrakeImbalanceAxle2($serviceBrakeData));
            $axle2ImbalanceWithinLimits = $serviceBrakeData->getImbalanceAxle2() <= self::IMBALANCE_MAXIMUM;
            $axle2ImbalanceOK = $axle2ImbalanceWithinLimits
                || (!$axle2ImbalanceWithinLimits
                    && $this->isWheelWithLowerEfficiencyLocked(
                        $serviceBrakeData->getEffortOffsideAxle2(),
                        $serviceBrakeData->getLockOffsideAxle2(),
                        $serviceBrakeData->getEffortNearsideAxle2(),
                        $serviceBrakeData->getLockNearsideAxle2()
                    ));
            $serviceBrakeData->setImbalancePassForAxle(2, $axle2ImbalanceOK);
            $imbalancePassing = $imbalancePassing && $axle2ImbalanceOK;
        }

        if ($shouldCalcImbalanceForAxle3) {
            $serviceBrakeData->setImbalanceAxle3($this->calculateServiceBrakeImbalanceAxle3($serviceBrakeData));
            $axle3ImbalanceWithinLimits = $serviceBrakeData->getImbalanceAxle3() <= self::IMBALANCE_MAXIMUM;
            $axle3ImbalanceOK = $axle3ImbalanceWithinLimits
                || (!$axle3ImbalanceWithinLimits
                    && $this->isWheelWithLowerEfficiencyLocked(
                        $serviceBrakeData->getEffortOffsideAxle3(),
                        $serviceBrakeData->getLockOffsideAxle3(),
                        $serviceBrakeData->getEffortNearsideAxle3(),
                        $serviceBrakeData->getLockNearsideAxle3()
                    ));
            $serviceBrakeData->setImbalancePassForAxle(3, $axle3ImbalanceOK);
            $imbalancePassing = $imbalancePassing && $axle3ImbalanceOK;
        }
        $serviceBrakeData->setImbalancePass($imbalancePassing);
    }

    protected function calculateServiceBrakeEfficiency(
        BrakeTestResultServiceBrakeData $serviceBrakeData,
        $weight,
        $wheelCount,
        $isSingleInFront
    ) {
        $effort = 0;

        if ($wheelCount === self::WHEEL_COUNT_3) {
            $effort += $serviceBrakeData->getEffortSingle();
            if ($isSingleInFront === true) {
                $effort += $serviceBrakeData->getEffortNearsideAxle2() + $serviceBrakeData->getEffortOffsideAxle2();
            } else {
                $effort += $serviceBrakeData->getEffortNearsideAxle1() + $serviceBrakeData->getEffortOffsideAxle1();
            }
        } else {
            $effort += $serviceBrakeData->getEffortNearsideAxle1() + $serviceBrakeData->getEffortOffsideAxle1();
            $effort += $serviceBrakeData->getEffortNearsideAxle2() + $serviceBrakeData->getEffortOffsideAxle2();
            if ($wheelCount === self::WHEEL_COUNT_6) {
                $effort += $serviceBrakeData->getEffortNearsideAxle3() + $serviceBrakeData->getEffortOffsideAxle3();
            }
        }

        return $this->calculateEfficiency($effort, $weight);
    }

    protected function calculateParkingBrakeEfficiency(BrakeTestResultClass3AndAbove $brakeTestResult)
    {
        $effort = $brakeTestResult->getParkingBrakeEffortNearside()
            + $brakeTestResult->getParkingBrakeEffortOffside()
            + $brakeTestResult->getParkingBrakeEffortSingle()
            + $brakeTestResult->getParkingBrakeEffortSecondaryNearside()
            + $brakeTestResult->getParkingBrakeEffortSecondaryOffside();
        $weight = $brakeTestResult->getVehicleWeight();

        return $this->calculateEfficiency($effort, $weight);
    }

    protected function calculateServiceBrakeImbalanceAxle1(BrakeTestResultServiceBrakeData $serviceBrakeData)
    {
        return $this->calculateImbalance(
            $serviceBrakeData->getEffortOffsideAxle1(),
            $serviceBrakeData->getEffortNearsideAxle1()
        );
    }

    protected function calculateServiceBrakeImbalanceAxle2(BrakeTestResultServiceBrakeData $serviceBrakeData)
    {
        return $this->calculateImbalance(
            $serviceBrakeData->getEffortOffsideAxle2(),
            $serviceBrakeData->getEffortNearsideAxle2()
        );
    }

    protected function calculateServiceBrakeImbalanceAxle3(BrakeTestResultServiceBrakeData $serviceBrakeData)
    {
        return $this->calculateImbalance(
            $serviceBrakeData->getEffortOffsideAxle3(),
            $serviceBrakeData->getEffortNearsideAxle3()
        );
    }

    //THIS FUNCTION RETURNS IMBALANCE FOR PRIMARY AXLE OF THE PARKING BRAKE
    protected function calculateParkingBrakeImbalance(BrakeTestResultClass3AndAbove $r)
    {
        return $this->calculateImbalance(
            $r->getParkingBrakeEffortOffside(),
            $r->getParkingBrakeEffortNearside()
        );
    }

    //THIS FUNCTION RETURNS IMBALANCE FOR SECONDARY AXLE OF THE PARKING BRAKE
    protected function calculateParkingBrakeSecondaryImbalance(BrakeTestResultClass3AndAbove $r)
    {
        return $this->calculateImbalance(
            $r->getParkingBrakeEffortSecondaryOffside(),
            $r->getParkingBrakeEffortSecondaryNearside()
        );
    }

    protected function calculateImbalance($effortOffside, $effortNearside)
    {
        $result = abs($effortNearside - $effortOffside);
        $maxEffortOnAxle = max(intval($effortOffside), intval($effortNearside));

        if ($maxEffortOnAxle === 0) {
            return 0;
        } else {
            $result /= $maxEffortOnAxle;
            $result *= 100;
            $result = round($result, 3);

            return ceil($result);
        }
    }

    abstract protected function isPassingServiceBrakeEfficiency(
        Vehicle $vehicle,
        $serviceBrakeTest,
        BrakeTestResultClass3AndAbove $brakeTestResult,
        $serviceBrakeNumber
    );

    abstract protected function isPassingParkingBrakeEfficiency(
        BrakeTestResultClass3AndAbove $brakeTestResult,
        $vehicleClass
    );

    abstract protected function isPassingParkingBrakeImbalance(
        BrakeTestResultClass3AndAbove $testResult,
        $vehicleClass
    );

    protected function isPassing(BrakeTestResultClass3AndAbove $brakeTestResult)
    {
        $serviceBrake1Pass = $brakeTestResult->getServiceBrake1EfficiencyPass();
        $serviceBrake2Pass = $brakeTestResult->getServiceBrake2EfficiencyPass();

        $serviceBrake1ImbalancePass = null;
        $serviceBrake2ImbalancePass = null;

        $serviceBrake1Data = $brakeTestResult->getServiceBrake1Data();
        $serviceBrake2Data = $brakeTestResult->getServiceBrake1Data();

        if ($serviceBrake1Data) {
            $serviceBrake1ImbalancePass = $serviceBrake1Data->getImbalancePass();
        }
        if ($serviceBrake2Data) {
            $serviceBrake2ImbalancePass = $serviceBrake2Data->getImbalancePass();
        }

        return $serviceBrake1Pass !== false
        && $serviceBrake2Pass !== false
        && $brakeTestResult->getParkingBrakeEfficiencyPass() !== false
        && $serviceBrake1ImbalancePass !== false
        && $serviceBrake2ImbalancePass !== false
        && $brakeTestResult->getParkingBrakeImbalancePass() !== false;
    }

    protected function resetCalculatedFields(BrakeTestResultClass3AndAbove $brakeTestResult)
    {
        $serviceBrake1TestType = $brakeTestResult->getServiceBrake1TestType()->getCode();
        switch ($serviceBrake1TestType) {
            case BrakeTestTypeCode::PLATE:
            case BrakeTestTypeCode::ROLLER:
                $brakeTestResult->setServiceBrake1Efficiency(null);
                $brakeTestResult->setServiceBrake1EfficiencyPass(false);
                break;
        }

        if ($serviceBrake1TestType === BrakeTestTypeCode::DECELEROMETER) {
            $brakeTestResult->setServiceBrake1EfficiencyPass(false);
        }

        $serviceBrake2TestType = null;
        if ($brakeTestResult->getServiceBrake2TestType() !== null) {
            $serviceBrake2TestType = $brakeTestResult->getServiceBrake2TestType()->getCode();
        }
        switch ($serviceBrake2TestType) {
            case BrakeTestTypeCode::PLATE:
            case BrakeTestTypeCode::ROLLER:
                $brakeTestResult->setServiceBrake2Efficiency(null);
                $brakeTestResult->setServiceBrake2EfficiencyPass(false);
                break;
        }

        if ($serviceBrake2TestType === BrakeTestTypeCode::DECELEROMETER) {
            $brakeTestResult->setServiceBrake2EfficiencyPass(null);
        }

        $brakeTestResult->setParkingBrakeImbalance(null);
        $brakeTestResult->setParkingBrakeImbalancePass(null);

        $parkingBrakeTestType = $brakeTestResult->getParkingBrakeTestType()->getCode();
        if ($this->isTestTypeSetsEfficiencyPass($parkingBrakeTestType)) {
            $brakeTestResult->setParkingBrakeEfficiencyPass(false);

            if ($this->isTestTypeSetsEfficiency($parkingBrakeTestType)) {
                $brakeTestResult->setParkingBrakeEfficiency(null);
            }
        }

        $brakeTestResult->setGeneralPass(null);
    }

    protected function resetCalculatedFieldsForServiceBrake(BrakeTestResultServiceBrakeData $serviceBrakeData)
    {
        $serviceBrakeData->setImbalancePass(null);
        $serviceBrakeData->setImbalanceAxle1(null);
        $serviceBrakeData->setImbalanceAxle2(null);
        $serviceBrakeData->setImbalanceAxle3(null);
    }

    protected function isTestTypeSetsEfficiencyPass($testType)
    {
        return in_array(
            $testType,
            [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::PLATE,
                BrakeTestTypeCode::DECELEROMETER,
            ]
        );
    }

    protected function isTestTypeSetsEfficiency($testType)
    {
        return in_array(
            $testType,
            [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::PLATE,
            ]
        );
    }

    protected function getWheelCount(BrakeTestResultClass3AndAbove $brakeTestResult, Vehicle $vehicle)
    {
        $serviceBrake1Data = $brakeTestResult->getServiceBrake1Data();
        if ($vehicle->getVehicleClass()->getCode() === Vehicle::VEHICLE_CLASS_3) {
            return self::WHEEL_COUNT_3;
        } elseif ($serviceBrake1Data->getEffortNearsideAxle3() !== null
            || $serviceBrake1Data->getEffortOffsideAxle3() !== null
        ) {
            return self::WHEEL_COUNT_6;
        } else {
            return self::WHEEL_COUNT_4;
        }
    }

    private function isWheelWithLowerEfficiencyLocked($offsideEff, $offsideLock, $nearsideEff, $nearsideLock)
    {
        $offsideLower = ($offsideEff < $nearsideEff);
        $nearsideLower = ($nearsideEff < $offsideEff);

        return ($offsideLower && $offsideLock) || ($nearsideLower && $nearsideLock);
    }
}
