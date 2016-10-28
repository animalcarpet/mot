<?php
/**
 * Created by PhpStorm.
 * User: Shankar
 * Date: 09/02/2016
 * Time: 11:43
 */

namespace Dvsa\Mot\Behat\Support\Api;


class RollerBrakeTestClass3To7
{
    private $weightType;
    private $serviceBrake;
    private $vehicleWeight;
    private $brakeLineType;
    private $numberOfAxles;
    private $weightIsUnladen;
    private $vehiclePurposeType;
    private $isCommercialVehicle;
    private $parkingBrakeTestType;
    private $positionOfSingleWheel;
    private $serviceBrake1TestType;
    private $serviceBrake2TestType;
    private $parkingBrakeLockSingle;
    private $parkingBrakeWheelsCount;
    private $parkingBrakeLockOffside;
    private $serviceBrakeIsSingleLine;
    private $parkingBrakeEffortSingle;
    private $parkingBrakeLockNearside;
    private $serviceBrakeControlsCount;
    private $parkingBrakeNumberOfAxles;
    private $parkingBrakeEffortOffside;
    private $parkingBrakeEffortNearside;
    private $effortNearsideAxle1;
    private $effortNearsideAxle2;
    private $effortOffsideAxle1;
    private $effortOffsideAxle2;
    private $lockOffsideAxle1;
    private $lockOffsideAxle2;
    private $lockNearsideAxle2;
    private $lockNearsideAxle1;

    /**
     * RollerBrakeTestClass3To7 constructor.
     * @param array $array
     */
    public function __construct($array)
    {
        $this->weightType                  = $array['weightType'];
        $this->serviceBrake                = $array['serviceBrake1Data'];
        $this->vehicleWeight               = $array['vehicleWeight'];
        $this->brakeLineType               = $array['brakeLineType'];
        $this->numberOfAxles               = $array['numberOfAxles'];
        $this->weightIsUnladen             = $array['weightIsUnladen'];
        $this->vehiclePurposeType          = $array['vehiclePurposeType'];
        $this->isCommercialVehicle         = $array['isCommercialVehicle'];
        $this->parkingBrakeTestType        = $array['parkingBrakeTestType'];
        $this->positionOfSingleWheel       = $array['positionOfSingleWheel'];
        $this->serviceBrake1TestType       = $array['serviceBrake1TestType'];
        $this->serviceBrake2TestType       = $array['serviceBrake2TestType'];
        $this->parkingBrakeLockSingle      = $array['parkingBrakeLockSingle'];
        $this->parkingBrakeWheelsCount     = $array['parkingBrakeWheelsCount'];
        $this->parkingBrakeLockOffside     = $array['parkingBrakeLockOffside'];
        $this->serviceBrakeIsSingleLine    = $array['serviceBrakeIsSingleLine'];
        $this->parkingBrakeEffortSingle    = $array['parkingBrakeEffortSingle'];
        $this->parkingBrakeLockNearside    = $array['parkingBrakeLockNearside'];
        $this->serviceBrakeControlsCount   = $array['serviceBrakeControlsCount'];
        $this->parkingBrakeNumberOfAxles   = $array['parkingBrakeNumberOfAxles'];
        $this->parkingBrakeEffortOffside   = $array['parkingBrakeEffortOffside'];
        $this->parkingBrakeEffortNearside  = $array['parkingBrakeEffortNearside'];

        $this->effortNearsideAxle2         = $array['serviceBrake1Data']['effortNearsideAxle2'];
        $this->effortNearsideAxle1         = $array['serviceBrake1Data']['effortNearsideAxle1'];
        $this->effortOffsideAxle1          = $array['serviceBrake1Data']['effortOffsideAxle1'];
        $this->effortOffsideAxle2          = $array['serviceBrake1Data']['effortOffsideAxle2'];
        $this->lockOffsideAxle1            = $array['serviceBrake1Data']['lockOffsideAxle1'];
        $this->lockOffsideAxle2            = $array['serviceBrake1Data']['lockOffsideAxle2'];
        $this->lockNearsideAxle2           = $array['serviceBrake1Data']['lockNearsideAxle2'];
        $this->lockNearsideAxle1           = $array['serviceBrake1Data']['lockNearsideAxle1'];
    }

    /**
     * @return mixed
     */
    public function getWeightType()
    {
        return $this->weightType;
    }

    /**
     * @return mixed
     */
    public function getVehicleWeight()
    {
        return $this->vehicleWeight;
    }

    /**
     * @return mixed
     */
    public function getBrakeLineType()
    {
        return $this->brakeLineType;
    }

    /**
     * @return mixed
     */
    public function getNumberOfAxles()
    {
        return $this->numberOfAxles;
    }

    /**
     * @return mixed
     */
    public function getWeightIsUnladen()
    {
        return $this->weightIsUnladen;
    }

    /**
     * @return mixed
     */
    public function getVehiclePurposeType()
    {
        return $this->vehiclePurposeType;
    }

    /**
     * @return mixed
     */
    public function getIsCommercialVehicle()
    {
        return $this->isCommercialVehicle;
    }

    /**
     * @return mixed
     */
    public function getPositionOfSingleWheel()
    {
        return $this->positionOfSingleWheel;
    }

    /**
     * @return mixed
     */
    public function getParkingBrakeWheelsCount()
    {
        return $this->parkingBrakeWheelsCount;
    }

    /**
     * @return mixed
     */
    public function getParkingBrakeLockOffside()
    {
        return $this->parkingBrakeLockOffside;
    }

    /**
     * @return mixed
     */
    public function getParkingBrakeLockNearside()
    {
        return $this->parkingBrakeLockNearside;
    }

    /**
     * @return mixed
     */
    public function getServiceBrakeControlsCount()
    {
        return $this->serviceBrakeControlsCount;
    }

    /**
     * @return mixed
     */
    public function getParkingBrakeNumberOfAxles()
    {
        return $this->parkingBrakeNumberOfAxles;
    }

    /**
     * @return mixed
     */
    public function getParkingBrakeEffortNearside()
    {
        return $this->parkingBrakeEffortNearside;
    }

    /**
     * @return mixed
     */
    public function getParkingBrakeEffortOffside()
    {
        return $this->parkingBrakeEffortOffside;
    }

    /**
     * @return mixed
     */
    public function getParkingBrakeEffortSingle()
    {
        return $this->parkingBrakeEffortSingle;
    }

    /**
     * @return mixed
     */
    public function getServiceBrakeIsSingleLine()
    {
        return $this->serviceBrakeIsSingleLine;
    }

    /**
     * @return mixed
     */
    public function getParkingBrakeLockSingle()
    {
        return $this->parkingBrakeLockSingle;
    }

    /**
     * @return mixed
     */
    public function getServiceBrake1TestType()
    {
        return $this->serviceBrake1TestType;
    }

    /**
     * @return mixed
     */
    public function getParkingBrakeTestType()
    {
        return $this->parkingBrakeTestType;
    }

    /**
     * @return mixed
     */
    public function getEffortNearsideAxle1()
    {
        return $this->effortNearsideAxle1;
    }

    /**
     * @return mixed
     */
    public function getEffortNearsideAxle2()
    {
        return $this->effortNearsideAxle2;
    }

    /**
     * @return mixed
     */
    public function getEffortOffsideAxle1()
    {
        return $this->effortOffsideAxle1;
    }

    /**
     * @return mixed
     */
    public function getEffortOffsideAxle2()
    {
        return $this->effortOffsideAxle2;
    }

    /**
     * @return mixed
     */
    public function getLockOffsideAxle1()
    {
        return $this->lockOffsideAxle1;
    }

    /**
     * @return mixed
     */
    public function getLockOffsideAxle2()
    {
        return $this->lockOffsideAxle2;
    }


    /**
     * @return mixed
     */
    public function getLockNearsideAxle2()
    {
        return $this->lockNearsideAxle2;
    }

    /**
     * @return mixed
     */
    public function getLockNearsideAxle1()
    {
        return $this->lockNearsideAxle1;
    }
}