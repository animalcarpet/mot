<?php

namespace DvsaCommon\Dto\BrakeTest;

use DvsaCommon\Dto\AbstractDataTransferObject;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationDtoInterface as DtoInterface;

/**
 * Dto for transferring class 3 and above brake configurations
 */
class BrakeTestConfigurationClass3AndAboveDto extends AbstractDataTransferObject implements DtoInterface
{
    protected $serviceBrake1TestType;
    protected $serviceBrake2TestType;
    protected $parkingBrakeTestType;
    protected $weightType;
    protected $vehicleWeight;
    protected $weightIsUnladen;
    protected $serviceBrakeIsSingleLine;
    protected $isCommercialVehicle;
    protected $isSingleInFront;
    protected $isParkingBrakeOnTwoWheels;
    protected $serviceBrakeControlsCount;
    protected $numberOfAxles;
    protected $parkingBrakeNumberOfAxles;
    protected $vehicleClass;

    /**
     * @param boolean $isCommercialVehicle
     *
     * @return $this
     */
    public function setIsCommercialVehicle($isCommercialVehicle)
    {
        $this->isCommercialVehicle = $isCommercialVehicle;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsCommercialVehicle()
    {
        return $this->isCommercialVehicle;
    }

    /**
     * @param boolean $isParkingBrakeOnTwoWheels
     *
     * @return $this
     */
    public function setIsParkingBrakeOnTwoWheels($isParkingBrakeOnTwoWheels)
    {
        $this->isParkingBrakeOnTwoWheels = $isParkingBrakeOnTwoWheels;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsParkingBrakeOnTwoWheels()
    {
        return $this->isParkingBrakeOnTwoWheels;
    }

    /**
     * @param boolean $isSingleInFront
     *
     * @return $this
     */
    public function setIsSingleInFront($isSingleInFront)
    {
        $this->isSingleInFront = $isSingleInFront;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsSingleInFront()
    {
        return $this->isSingleInFront;
    }

    /**
     * @param int $numberOfAxles
     *
     * @return $this
     */
    public function setNumberOfAxles($numberOfAxles)
    {
        $this->numberOfAxles = $numberOfAxles;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfAxles()
    {
        return $this->numberOfAxles;
    }

    /**
     * @param int $parkingBrakeNumberOfAxles
     *
     * @return $this
     */
    public function setParkingBrakeNumberOfAxles($parkingBrakeNumberOfAxles)
    {
        $this->parkingBrakeNumberOfAxles = $parkingBrakeNumberOfAxles;
        return $this;
    }

    /**
     * @return int
     */
    public function getParkingBrakeNumberOfAxles()
    {
        return $this->parkingBrakeNumberOfAxles;
    }

    /**
     * @param string $parkingBrakeTestType
     *
     * @return $this
     */
    public function setParkingBrakeTestType($parkingBrakeTestType)
    {
        $this->parkingBrakeTestType = $parkingBrakeTestType;
        return $this;
    }

    /**
     * @return string
     */
    public function getParkingBrakeTestType()
    {
        return $this->parkingBrakeTestType;
    }

    /**
     * @param string $serviceBrake1TestType
     *
     * @return $this
     */
    public function setServiceBrake1TestType($serviceBrake1TestType)
    {
        $this->serviceBrake1TestType = $serviceBrake1TestType;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceBrake1TestType()
    {
        return $this->serviceBrake1TestType;
    }

    /**
     * @param string $serviceBrake2TestType
     *
     * @return $this
     */
    public function setServiceBrake2TestType($serviceBrake2TestType)
    {
        $this->serviceBrake2TestType = $serviceBrake2TestType;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceBrake2TestType()
    {
        return $this->serviceBrake2TestType;
    }

    /**
     * @param int $serviceBrakeControlsCount
     *
     * @return $this
     */
    public function setServiceBrakeControlsCount($serviceBrakeControlsCount)
    {
        $this->serviceBrakeControlsCount = $serviceBrakeControlsCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getServiceBrakeControlsCount()
    {
        return $this->serviceBrakeControlsCount;
    }

    /**
     * @param boolean $serviceBrakeIsSingleLine
     *
     * @return $this
     */
    public function setServiceBrakeIsSingleLine($serviceBrakeIsSingleLine)
    {
        $this->serviceBrakeIsSingleLine = $serviceBrakeIsSingleLine;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getServiceBrakeIsSingleLine()
    {
        return $this->serviceBrakeIsSingleLine;
    }

    /**
     * @param int $vehicleClass
     *
     * @return $this
     */
    public function setVehicleClass($vehicleClass)
    {
        $this->vehicleClass = $vehicleClass;
        return $this;
    }

    /**
     * @return int
     */
    public function getVehicleClass()
    {
        return $this->vehicleClass;
    }

    /**
     * @param string $vehicleWeight
     *
     * @return $this
     */
    public function setVehicleWeight($vehicleWeight)
    {
        $this->vehicleWeight = $vehicleWeight;
        return $this;
    }

    /**
     * @return string
     */
    public function getVehicleWeight()
    {
        return $this->vehicleWeight;
    }

    /**
     * @param boolean $weightIsUnladen
     *
     * @return $this
     */
    public function setWeightIsUnladen($weightIsUnladen)
    {
        $this->weightIsUnladen = $weightIsUnladen;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getWeightIsUnladen()
    {
        return $this->weightIsUnladen;
    }

    /**
     * @param string $weightType
     *
     * @return $this
     */
    public function setWeightType($weightType)
    {
        $this->weightType = $weightType;
        return $this;
    }

    /**
     * @return string
     */
    public function getWeightType()
    {
        return $this->weightType;
    }
}
