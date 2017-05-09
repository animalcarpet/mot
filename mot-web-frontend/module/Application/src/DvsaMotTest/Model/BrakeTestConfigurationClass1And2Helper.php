<?php

namespace DvsaMotTest\Model;

use DvsaCommon\Constants\BrakeTestConfigurationClass1And2;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass1And2Dto;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationDtoInterface as ConfigDto;
use DvsaCommon\Enum\BrakeTestTypeCode;

/**
 * Data model for configuration of brake test (vehicle class 1 and 2).
 */
class BrakeTestConfigurationClass1And2Helper implements BrakeTestConfigurationHelperInterface
{
    /**
     * @var BrakeTestConfigurationClass1And2Dto
     */
    private $configDto;

    public function __construct(ConfigDto $configDto = null)
    {
        if (isset($configDto)) {
            $this->setConfigDto($configDto);
        }
    }

    /**
     * @return ConfigDto
     */
    public function getConfigDto()
    {
        return $this->configDto;
    }

    /**
     * @param ConfigDto $configDto
     */
    public function setConfigDto(ConfigDto $configDto)
    {
        $this->configDto = $configDto;
    }

    /**
     * @return bool
     */
    public function requiresWeight()
    {
        return in_array(
            $this->configDto->getBrakeTestType(),
            [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::PLATE,
                BrakeTestTypeCode::FLOOR,
            ]
        );
    }

    /**
     * @return bool
     */
    public function locksApplicableToTestType()
    {
        return BrakeTestConfigurationClass1And2::isLockApplicableToTestType($this->configDto->getBrakeTestType());
    }

    /**
     * @return bool
     */
    public function requiresEffortsTypeTest()
    {
        return $this->configDto->getBrakeTestType() === BrakeTestTypeCode::ROLLER
        || $this->configDto->getBrakeTestType() === BrakeTestTypeCode::PLATE;
    }

    /**
     * @return bool
     */
    public function isGradientTypeTest()
    {
        return $this->configDto->getBrakeTestType() === BrakeTestTypeCode::GRADIENT;
    }

    /**
     * @return bool
     */
    public function isFloorTypeTest()
    {
        return $this->configDto->getBrakeTestType() === BrakeTestTypeCode::FLOOR;
    }

    /**
     * @return bool
     */
    public function isDecelerometerTypeTest()
    {
        return $this->configDto->getBrakeTestType() === BrakeTestTypeCode::DECELEROMETER;
    }

    /**
     * @return string
     */
    public function getBrakeTestType()
    {
        return $this->configDto->getBrakeTestType();
    }

    /**
     * @return bool
     */
    public function isSidecarAttached()
    {
        return $this->configDto->getIsSidecarAttached();
    }

    /**
     * @return string
     */
    public function getRiderWeight()
    {
        return $this->configDto->getRiderWeight();
    }

    /**
     * @return string
     */
    public function getSidecarWeight()
    {
        return $this->configDto->getSidecarWeight();
    }

    /**
     * @return string
     */
    public function getVehicleWeightFront()
    {
        return $this->configDto->getVehicleWeightFront();
    }

    /**
     * @return string
     */
    public function getVehicleWeightRear()
    {
        return $this->configDto->getVehicleWeightRear();
    }
}
