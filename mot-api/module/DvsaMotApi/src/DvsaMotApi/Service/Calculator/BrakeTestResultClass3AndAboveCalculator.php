<?php

namespace DvsaMotApi\Service\Calculator;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Domain\BrakeTestTypeConfiguration;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\BrakeTestResultServiceBrakeData;
use DvsaEntities\Entity\Vehicle;
use DvsaFeature\FeatureToggles;

/**
 * Class BrakeTestResultClass3AndAboveCalculator.
 */
class BrakeTestResultClass3AndAboveCalculator extends BrakeTestResultClass3AndAboveCalculatorBase
{
    const LOCKS_MINIMUM = 50;

    const EFFORT_MINIMUM_REAR_WHEELS_CLASS_7_FRONT_LOCKED_2_AXLE = 100;
    const EFFORT_MINIMUM_REAR_WHEELS_CLASS_7_FRONT_LOCKED_3_AXLE = 50;

    const EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES = 50;
    const EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010 = 58;
    const EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010_COMMERCIAL = 50;
    const EFFICIENCY_PARKING_BRAKE_CLASS_4 = 16;
    const EFFICIENCY_SERVICE_BRAKE_CLASS_3_PRE_1968 = 40;
    const EFFICIENCY_PARKING_BRAKE_SINGLE_LINE = 25;
    const EFFICIENCY_PARKING_BRAKE_SINGLE_LINE_DANGEROUS = 12;
    const EFFICIENCY_PARKING_BRAKE_DUAL_LINE = 16;
    const EFFICIENCY_PARKING_BRAKE_DUAL_LINE_DANGEROUS = 8;
    const EFFICIENCY_TWO_SERVICE_BRAKES_PRIMARY = 30;
    const EFFICIENCY_TWO_SERVICE_BRAKES_PRIMARY_EU_DANGEROUS_THRESHOLD = 15;
    const EFFICIENCY_TWO_SERVICE_BRAKES_SECONDARY = 25;
    const EFFICIENCY_TWO_SERVICE_BRAKES_SECONDARY_EU_DANGEROUS_THRESHOLD  = 12.5;

    /**
     * @param FeatureToggles $featureToggles
     */
    public function __construct(FeatureToggles $featureToggles)
    {
        $this->featureToggles = $featureToggles;
    }

    /**
     * This value is calculated once and cached in BrakeTestResultClass3AndAbove.
     *
     * @param Vehicle                       $vehicle
     * @param string                        $serviceBrakeType
     * @param BrakeTestResultClass3AndAbove $brakeTestResult
     * @param int                           $serviceBrakeNumber
     *
     * @return ServiceBrakeCalculationResult
     */
    public function createServiceBrakeCalculationResult(
        Vehicle $vehicle,
        $serviceBrakeType,
        BrakeTestResultClass3AndAbove $brakeTestResult,
        $serviceBrakeNumber
    ) {
        $serviceBrakeEfficiencyPassing = false;
        $calculationFailingSeverity = CalculationFailureSeverity::DANGEROUS;

        $isCheckingServiceBrake1 = ($serviceBrakeNumber === self::SERVICE_BRAKE_1);

        $vehicleClassCode = $vehicle->getVehicleClass()->getCode();
        $parkingBrakeType = $brakeTestResult->getParkingBrakeTestType()->getCode();
        $lockCheckApplicable = BrakeTestTypeConfiguration::areServiceBrakeLocksApplicable(
            $vehicleClassCode,
            $serviceBrakeType,
            $parkingBrakeType
        );

        if ($isCheckingServiceBrake1) {
            $checkedServiceBrake = $brakeTestResult->getServiceBrake1Data();
            $checkedEfficiency = $brakeTestResult->getServiceBrake1Efficiency();
            $secondEfficiency = $brakeTestResult->getServiceBrake2Efficiency();
        } else {
            $checkedServiceBrake = $brakeTestResult->getServiceBrake2Data();
            $checkedEfficiency = $brakeTestResult->getServiceBrake2Efficiency();
            $secondEfficiency = $brakeTestResult->getServiceBrake1Efficiency();
        }
        $hasTwoServiceBrakes = $this->hasTwoServiceBrakes($brakeTestResult);
        if ($isCheckingServiceBrake1 && !$hasTwoServiceBrakes) {
            //FIRST SERVICE BRAKE for ONE SERVICE BRAKE VEHICLES
            $efficiencyThreshold = $this->getEfficiencyThreshold($vehicle, $brakeTestResult);
            $euDangerousThreshold  = $efficiencyThreshold / 2;

            //check efficiency
            $efficiencyPassing = $checkedEfficiency >= $efficiencyThreshold;
            //check locks
            $locksPassing = $lockCheckApplicable && $checkedServiceBrake != null ?
                $this->isPassingOnLocks($checkedServiceBrake, $brakeTestResult) : false;
            $serviceBrakeEfficiencyPassing = $serviceBrakeEfficiencyPassing || $locksPassing || $efficiencyPassing;
            //check locks on class 7
            if (in_array($vehicle->getVehicleClass()->getCode(), [Vehicle::VEHICLE_CLASS_7])) {
                $frontLocksPassing = $lockCheckApplicable && $this->isPassingFrontWheelsLockedRearEfficiencyClass7(
                    $brakeTestResult,
                    $checkedServiceBrake
                );

                $serviceBrakeEfficiencyPassing = $serviceBrakeEfficiencyPassing || $frontLocksPassing;
            }

            $calculationFailingSeverity = $this->determinateFailureSeverityForSingleServiceBrake($serviceBrakeEfficiencyPassing, $checkedEfficiency, $euDangerousThreshold);

        } else {
            //TWO SERVICE BRAKE VEHICLES
            if ($this->isSecondServiceBrakeApplicableToClass($vehicle->getVehicleClass()->getCode())) {
                //check efficiency
                $efficiencyPassing = $this->checkEfficiencyForTwoServiceBrakes($checkedEfficiency, $secondEfficiency);
                $dangerousEfficiencyPassing = $this->checkIfEfficiencyForTwoServiceBrakesIsPassingDangerousLevelThreshold($checkedEfficiency, $secondEfficiency);

                //check locks
                $locksPassing = $lockCheckApplicable ?
                    $this->isPassingOnLocks($checkedServiceBrake, $brakeTestResult) : false;
                $serviceBrakeEfficiencyPassing = $efficiencyPassing || $locksPassing;

                $calculationFailingSeverity = $this->determinateFailureSeverityForTwoServiceBrake($serviceBrakeEfficiencyPassing, $dangerousEfficiencyPassing);
            }
        }

        if ($this->isUnladenVehicleClass7($vehicle, $brakeTestResult)) {
            $results = $brakeTestResult->getServiceBrake1Data();

            if (
                ($results->getEffortNearsideAxle1() >= 100 && $results->getLockNearsideAxle1())
                && ($results->getEffortOffsideAxle1() >= 100 && $results->getLockOffsideAxle1())
                && ($results->getEffortNearsideAxle2() >= 50 && $results->getEffortOffsideAxle2() >= 50)
                && ($results->getEffortNearsideAxle3() >= 50 && $results->getEffortOffsideAxle3() >= 50)
            ) {
                $serviceBrakeEfficiencyPassing = true;
                $calculationFailingSeverity = CalculationFailureSeverity::NONE;
            }
        }

        $serviceBrakeCalculationResult = new ServiceBrakeCalculationResult(
            $serviceBrakeEfficiencyPassing,
            $calculationFailingSeverity
        );

        return $serviceBrakeCalculationResult;
    }

    private function isUnladenVehicleClass7(Vehicle $vehicle, BrakeTestResultClass3AndAbove $brakeTestResult)
    {
        return $vehicle->getVehicleClass()->getCode() === VehicleClassCode::CLASS_7
            && $brakeTestResult->getWeightIsUnladen();
    }

    /**
     * @param BrakeTestResultClass3AndAbove $brakeTestResult
     * @return ParkingBrakeCalculationResult
     */
    public function createParkingBrakeCalculationResult(BrakeTestResultClass3AndAbove $brakeTestResult)
    {
        $checkedEfficiency = $brakeTestResult->getParkingBrakeEfficiency();
        $percentLocked = $this->calculateParkingBrakePercentLocked($brakeTestResult);

        if ($percentLocked <= self::LOCKS_MINIMUM) {
            if ($brakeTestResult->getServiceBrakeIsSingleLine()) {
                $parkingBrakePasses = $checkedEfficiency >= self::EFFICIENCY_PARKING_BRAKE_SINGLE_LINE;
            } else {
                $parkingBrakePasses = $checkedEfficiency >= self::EFFICIENCY_PARKING_BRAKE_DUAL_LINE;
            }

            $failureSeverityType = $brakeTestResult->getServiceBrakeIsSingleLine()
                ? $this->determineFailureSeverityForSingleLineParkingBrake($parkingBrakePasses, $checkedEfficiency)
                : $this->determineFailureSeverityForDualLineParkingBrake($parkingBrakePasses, $checkedEfficiency);
        } else {
            $failureSeverityType = CalculationFailureSeverity::NONE;
            $parkingBrakePasses = true;
        }

        $parkingBrakeCalculationResult = new ParkingBrakeCalculationResult(
            $parkingBrakePasses,
            $failureSeverityType
        );

        return $parkingBrakeCalculationResult;
    }

    private function isPassingOnLocks(
        BrakeTestResultServiceBrakeData $checkedServiceBrake,
        BrakeTestResultClass3AndAbove $brakeTestResult
    ) {
        $percentLocked = $this->calculateServiceBrakePercentLocked($checkedServiceBrake, $brakeTestResult);

        return $percentLocked > self::LOCKS_MINIMUM;
    }

    private function hasTwoServiceBrakes(BrakeTestResultClass3AndAbove $brakeTestResult)
    {
        return $brakeTestResult->getServiceBrake2Data() !== null
        || ($brakeTestResult->getServiceBrake2TestType() !== null &&
            $brakeTestResult->getServiceBrake2TestType()->getCode() === BrakeTestTypeCode::DECELEROMETER
            && $brakeTestResult->getServiceBrake2Efficiency() !== null);
    }

    private function isSecondServiceBrakeApplicableToClass($vehicleClass)
    {
        return in_array($vehicleClass, [Vehicle::VEHICLE_CLASS_3]);
    }

    private function getEfficiencyThreshold(Vehicle $vehicle, BrakeTestResultClass3AndAbove $brakeTestResult)
    {
        $class3LimitDate = DateUtils::toDate(
            BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY
        );
        $class4LimitDate = DateUtils::toDate(
            BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY
        );
        if ($vehicle->getVehicleClass()->getCode() === Vehicle::VEHICLE_CLASS_3
            && $vehicle->getFirstUsedDate() < $class3LimitDate
        ) {
            return self::EFFICIENCY_SERVICE_BRAKE_CLASS_3_PRE_1968;
        } elseif ($vehicle->getVehicleClass()->getCode() === Vehicle::VEHICLE_CLASS_4
            && $vehicle->getFirstUsedDate() >= $class4LimitDate
        ) {
            if ($brakeTestResult->getIsCommercialVehicle() === true) {
                return self::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010_COMMERCIAL;
            } else {
                return self::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010;
            }
        } else {
            return self::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES;
        }
    }

    private function isPassingFrontWheelsLockedRearEfficiencyClass7(
        BrakeTestResultClass3AndAbove $brakeTestResult,
        BrakeTestResultServiceBrakeData $serviceBrakeData
    ) {
        $rearEfforts = [];
        $rearEfforts[] = $serviceBrakeData->getEffortNearsideAxle2();
        $rearEfforts[] = $serviceBrakeData->getEffortOffsideAxle2();
        $rearEfforts[] = $serviceBrakeData->getEffortNearsideAxle3();
        $rearEfforts[] = $serviceBrakeData->getEffortOffsideAxle3();
        $effortsAboveThreshold = true;

        if ($serviceBrakeData->getEffortNearsideAxle3() == null && $serviceBrakeData->getEffortOffsideAxle3() == null) {
            $minimumEffort = self::EFFORT_MINIMUM_REAR_WHEELS_CLASS_7_FRONT_LOCKED_2_AXLE;
        } else {
            $minimumEffort = self::EFFORT_MINIMUM_REAR_WHEELS_CLASS_7_FRONT_LOCKED_3_AXLE;
        }

        foreach ($rearEfforts as $effort) {
            if ($effort !== null && $effort < $minimumEffort) {
                $effortsAboveThreshold = false;
                break;
            }
        }

        return $brakeTestResult->getWeightIsUnladen() === true
        && $serviceBrakeData->getLockNearsideAxle1() === true
        && $serviceBrakeData->getLockOffsideAxle1() === true
        && $effortsAboveThreshold;
    }

    protected function isPassingParkingBrakeImbalance(
        BrakeTestResultClass3AndAbove $testResult,
        $vehicleClass
    ) {
        $imbalanceValuesPassing = $testResult->getParkingBrakeImbalance() <= self::IMBALANCE_MAXIMUM
            && $testResult->getParkingBrakeSecondaryImbalance() <= self::IMBALANCE_MAXIMUM;

        return $vehicleClass === Vehicle::VEHICLE_CLASS_3
        || !$testResult->getServiceBrakeIsSingleLine()
        || $imbalanceValuesPassing;
    }

    /**
     * New logic to determinate if given service brake is failing with a DANGEROUS severity
     *
     * const EFFICIENCY_TWO_SERVICE_BRAKES_SECONDARY_EU_DANGEROUS_THRESHOLD = 12.5
     * but efficiency % values are stored in DB as INTs so there is no way to get 12.5% it's either 12 or 13
     * calculated values are casted to INT: 12.999% -> 12%
     *
     * @param $checkedEfficiency
     * @param $secondEfficiency
     * @return bool
     */
    private function checkIfEfficiencyForTwoServiceBrakesIsPassingDangerousLevelThreshold($checkedEfficiency, $secondEfficiency)
    {
        return $checkedEfficiency >= self::EFFICIENCY_TWO_SERVICE_BRAKES_PRIMARY_EU_DANGEROUS_THRESHOLD
        || ($secondEfficiency >= self::EFFICIENCY_TWO_SERVICE_BRAKES_PRIMARY_EU_DANGEROUS_THRESHOLD
            && $checkedEfficiency >= self::EFFICIENCY_TWO_SERVICE_BRAKES_SECONDARY_EU_DANGEROUS_THRESHOLD);
    }

    /**
     * Old logic for determinate if service brake efficiency is good enough to pass.
     *
     * @param $checkedEfficiency
     * @param $secondEfficiency
     * @return bool
     */
    private function checkEfficiencyForTwoServiceBrakes($checkedEfficiency, $secondEfficiency)
    {
        return $checkedEfficiency >= self::EFFICIENCY_TWO_SERVICE_BRAKES_PRIMARY
            || ($secondEfficiency >= self::EFFICIENCY_TWO_SERVICE_BRAKES_PRIMARY
                && $checkedEfficiency >= self::EFFICIENCY_TWO_SERVICE_BRAKES_SECONDARY);
    }

    /**
     * Whenever a given service/parking brake test is failing we need to determinate the severity to create appropriate RFR
     * with appropriate EU rfr deficiency category set.
     *
     * @param bool $isItPassing
     * @param int|float $checkedEfficiency
     * @param int|float $euDangerousThreshold
     *
     * @return string
     */
    private function determinateFailureSeverityForSingleServiceBrake($isItPassing, $checkedEfficiency, $euDangerousThreshold)
    {
        if(true === $isItPassing){
            return CalculationFailureSeverity::NONE;
        }

        if($checkedEfficiency < $euDangerousThreshold) {
            return CalculationFailureSeverity::DANGEROUS;
        }

        return CalculationFailureSeverity::MAJOR;
    }

    /**
     * @param bool $serviceBrakeEfficiencyPassing
     * @param bool $dangerousEfficiencyPassing
     * @return string
     */
    protected function determinateFailureSeverityForTwoServiceBrake($serviceBrakeEfficiencyPassing, $dangerousEfficiencyPassing)
    {
        if (true === $serviceBrakeEfficiencyPassing) {
            return CalculationFailureSeverity::NONE;
        }

        if (false === $dangerousEfficiencyPassing) {
            return CalculationFailureSeverity::DANGEROUS;
        }

        return CalculationFailureSeverity::MAJOR;
    }

    private function determineFailureSeverityForSingleLineParkingBrake($isPassing, $checkedEfficiency) {
        if ($isPassing) {
            return CalculationFailureSeverity::NONE;
        }

        if ($checkedEfficiency < self::EFFICIENCY_PARKING_BRAKE_SINGLE_LINE_DANGEROUS) {
            return CalculationFailureSeverity::DANGEROUS;
        }

        return CalculationFailureSeverity::MAJOR;
    }

    private function determineFailureSeverityForDualLineParkingBrake($isPassing, $checkedEfficiency) {
        if ($isPassing) {
            return CalculationFailureSeverity::NONE;
        }

        if ($checkedEfficiency < self::EFFICIENCY_PARKING_BRAKE_DUAL_LINE_DANGEROUS) {
            return CalculationFailureSeverity::DANGEROUS;
        }

        return CalculationFailureSeverity::MAJOR;
    }
}
