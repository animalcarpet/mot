<?php
namespace DvsaMotApi\Service\Calculator;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Domain\BrakeTestTypeConfiguration;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\BrakeTestResultServiceBrakeData;
use DvsaEntities\Entity\Vehicle;

/**
 * Class BrakeTestResultClass3AndAboveCalculator
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
    const EFFICIENCY_PARKING_BRAKE_DUAL_LINE = 16;
    const EFFICIENCY_TWO_SERVICE_BRAKES_PRIMARY = 30;
    const EFFICIENCY_TWO_SERVICE_BRAKES_SECONDARY = 25;

    /**
     * This value is calculated once and cached in BrakeTestResultClass3AndAbove
     *
     * @param Vehicle $vehicle
     * @param                               $serviceBrakeType
     * @param BrakeTestResultClass3AndAbove $brakeTestResult
     * @param                               $serviceBrakeNumber
     *
     * @return bool
     */
    protected function isPassingServiceBrakeEfficiency(
        Vehicle $vehicle,
        $serviceBrakeType,
        BrakeTestResultClass3AndAbove $brakeTestResult,
        $serviceBrakeNumber
    ) {
        $serviceBrakeEfficiencyPassing = false;

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
            //check efficiency
            $efficiencyPassing = $checkedEfficiency >= $efficiencyThreshold;
            //check locks
            $locksPassing = $lockCheckApplicable ?
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
        } else {
            //TWO SERVICE BRAKE VEHICLES
            if ($this->isSecondServiceBrakeApplicableToClass($vehicle->getVehicleClass()->getCode())) {
                //check efficiency
                $efficiencyPassing = $checkedEfficiency >= self::EFFICIENCY_TWO_SERVICE_BRAKES_PRIMARY
                    || ($secondEfficiency >= self::EFFICIENCY_TWO_SERVICE_BRAKES_PRIMARY
                        && $checkedEfficiency >= self::EFFICIENCY_TWO_SERVICE_BRAKES_SECONDARY);
                //check locks
                $locksPassing = $lockCheckApplicable ?
                    $this->isPassingOnLocks($checkedServiceBrake, $brakeTestResult) : false;
                $serviceBrakeEfficiencyPassing = $efficiencyPassing || $locksPassing;
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
            }
        }

        return $serviceBrakeEfficiencyPassing;
    }

    private function isUnladenVehicleClass7(Vehicle $vehicle, BrakeTestResultClass3AndAbove $brakeTestResult)
    {
        return ($vehicle->getVehicleClass()->getCode() === VehicleClassCode::CLASS_7
            && $brakeTestResult->getWeightIsUnladen());
    }

    protected function isPassingParkingBrakeEfficiency(BrakeTestResultClass3AndAbove $brakeTestResult, $vehicleClass)
    {
        $percentLocked = $this->calculateParkingBrakePercentLocked($brakeTestResult);
        if ($brakeTestResult->getServiceBrakeIsSingleLine()) {
            $passesOnEfficiency
                = $brakeTestResult->getParkingBrakeEfficiency() >= self::EFFICIENCY_PARKING_BRAKE_SINGLE_LINE;
        } else {
            $passesOnEfficiency
                = $brakeTestResult->getParkingBrakeEfficiency() >= self::EFFICIENCY_PARKING_BRAKE_DUAL_LINE;
        }
        return $passesOnEfficiency || $percentLocked > self::LOCKS_MINIMUM;
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
}
