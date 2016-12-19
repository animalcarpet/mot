<?php

namespace Dvsa\Mot\Behat\Support\Data\Builder;

use DvsaCommon\Enum\BrakeTestTypeCode;

class Class3To7BrakeTestResultsBuilder
{
    private static $defaults = [
        'serviceBrake1Data' => [
            'effortNearsideAxle1' => 200,
            'effortOffsideAxle1' => 200,
            'lockNearsideAxle1' => false,
            'lockOffsideAxle1' => false,
            'effortNearsideAxle2' => 200,
            'effortOffsideAxle2' => 200,
            'lockNearsideAxle2' => false,
            'lockOffsideAxle2' => false,
        ],
        'parkingBrakeEffortSingle' => 200,
        'parkingBrakeLockSingle' => false,
        'parkingBrakeEffortNearside' => 200,
        'parkingBrakeEffortOffside' => 200,
        'parkingBrakeLockNearside' => false,
        'parkingBrakeLockOffside' => false,
        'serviceBrake1TestType' => BrakeTestTypeCode::ROLLER,
        'serviceBrake2TestType' => null,
        'parkingBrakeTestType' => BrakeTestTypeCode::ROLLER,
        'weightType' => 'vsi',
        'vehicleWeight' => 1000,
        'brakeLineType' => 'dual',
        'numberOfAxles' => 2,
        'parkingBrakeNumberOfAxles' => 1,
        'positionOfSingleWheel' => null,
        'parkingBrakeWheelsCount' => null,
        'serviceBrakeControlsCount' => null,
        'vehiclePurposeType' => null,
        'serviceBrakeIsSingleLine' => true,
        'weightIsUnladen' => false,
        'isCommercialVehicle' => false,
    ];

    private $data = [];

    public function withVehicleWeight($weight)
    {
        $this->data['vehicleWeight'] = $weight;

        return $this;
    }

    public function withAllEqualServiceBrakeEffort($effort)
    {
        $this->data['serviceBrake1Data']['effortNearsideAxle1'] = $effort;
        $this->data['serviceBrake1Data']['effortOffsideAxle1'] = $effort;
        $this->data['serviceBrake1Data']['effortNearsideAxle2'] = $effort;
        $this->data['serviceBrake1Data']['effortOffsideAxle2'] = $effort;

        return $this;
    }

    public function withAllEqualServiceBrakeWheelLocks($isLocked)
    {
        $this->data['serviceBrake1Data']['lockNearsideAxle1'] = $isLocked;
        $this->data['serviceBrake1Data']['lockOffsideAxle1'] = $isLocked;
        $this->data['serviceBrake1Data']['lockNearsideAxle2'] = $isLocked;
        $this->data['serviceBrake1Data']['lockOffsideAxle2'] = $isLocked;

        return $this;
    }

    public function withServiceBrakeTestType($testType)
    {
        $this->data['serviceBrake1TestType'] = $testType;

        return $this;
    }

    public function withParkingBrakeTestType($testType)
    {
        $this->data['parkingBrakeTestType'] = $testType;

        return $this;
    }

    public function build()
    {
        return array_merge(self::$defaults, $this->data);
    }
}
