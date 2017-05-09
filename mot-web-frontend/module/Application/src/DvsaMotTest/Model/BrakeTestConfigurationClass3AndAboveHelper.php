<?php

namespace DvsaMotTest\Model;

use DvsaCommon\Domain\BrakeTestTypeConfiguration;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationDtoInterface as ConfigDto;
use DvsaCommon\Enum\BrakeTestTypeCode;

/**
 * Class BrakeTestConfigurationClass3AndAboveHelper.
 */
class BrakeTestConfigurationClass3AndAboveHelper implements BrakeTestConfigurationHelperInterface
{
    const BRAKE_LINE_TYPE_SINGLE = 'single';
    const BRAKE_LINE_TYPE_DUAL = 'dual';

    const LOCATION_FRONT = 'front';
    const LOCATION_REAR = 'rear';

    const COUNT_ONE = 'one';
    const COUNT_TWO = 'two';

    const PURPOSE_COMMERCIAL = 'commercial';
    const PURPOSE_PERSONAL = 'personal';

    /**
     * @var BrakeTestConfigurationClass3AndAboveDto
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
    public function locksApplicableToFirstServiceBrake()
    {
        return BrakeTestTypeConfiguration::areServiceBrakeLocksApplicable(
            $this->configDto->getVehicleClass(),
            $this->configDto->getServiceBrake1TestType(),
            $this->configDto->getParkingBrakeTestType()
        );
    }

    /**
     * @return bool
     */
    public function locksApplicableToParkingBrake()
    {
        return in_array(
            $this->configDto->getParkingBrakeTestType(),
            [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE]
        );
    }

    /**
     * @return bool
     */
    public function effortsApplicableToFirstServiceBrake()
    {
        return $this->isRollerOrPlateType($this->configDto->getServiceBrake1TestType());
    }

    /**
     * @return bool
     */
    public function isParkingBrakeGradientType()
    {
        return $this->configDto->getParkingBrakeTestType() === BrakeTestTypeCode::GRADIENT;
    }

    /**
     * @return bool
     */
    public function isParkingBrakeTypeRollerOrPlate()
    {
        return $this->isRollerOrPlateType($this->configDto->getParkingBrakeTestType());
    }

    /**
     * @return int
     */
    public function getNumberOfAxles()
    {
        return $this->configDto->getNumberOfAxles();
    }

    /**
     * @return int
     */
    public function getParkingBrakeNumberOfAxles()
    {
        return $this->configDto->getParkingBrakeNumberOfAxles();
    }

    /**
     * @return string
     */
    public function getServiceBrakeTestType()
    {
        return $this->configDto->getServiceBrake1TestType();
    }

    /**
     * @return string
     */
    public function getParkingBrakeTestType()
    {
        return $this->configDto->getParkingBrakeTestType();
    }

    /**
     * @return string
     */
    public function getWeightType()
    {
        return $this->configDto->getWeightType();
    }

    /**
     * @return string
     */
    public function getVehicleWeight()
    {
        return $this->configDto->getVehicleWeight();
    }

    /**
     * @return bool
     */
    public function getWeightIsUnladen()
    {
        return $this->configDto->getWeightIsUnladen();
    }

    /**
     * @return string
     */
    public function getServiceBrakeLineType()
    {
        return $this->configDto->getServiceBrakeIsSingleLine() ? self::BRAKE_LINE_TYPE_SINGLE
            : self::BRAKE_LINE_TYPE_DUAL;
    }

    /**
     * @return string
     */
    public function getVehiclePurposeType()
    {
        return $this->configDto->getIsCommercialVehicle() ? self::PURPOSE_COMMERCIAL : self::PURPOSE_PERSONAL;
    }

    /**
     * @return string
     */
    public function getPositionOfSingleWheel()
    {
        return $this->configDto->getIsSingleInFront() ? self::LOCATION_FRONT : self::LOCATION_REAR;
    }

    /**
     * @return int
     */
    public function getParkingBrakeWheelsCount()
    {
        return $this->configDto->getIsParkingBrakeOnTwoWheels() ? 2 : 1;
    }

    /**
     * @return int
     */
    public function getServiceBrakeControlsCount()
    {
        return $this->configDto->getServiceBrakeControlsCount();
    }

    /**
     * @return bool
     */
    public function isSingleWheelInFront()
    {
        return $this->configDto->getIsSingleInFront();
    }

    /**
     * @return bool
     */
    public function isParkingBrakeOnTwoWheels()
    {
        return $this->configDto->getIsParkingBrakeOnTwoWheels();
    }

    /**
     * @return bool
     */
    public function hasTwoServiceBrakes()
    {
        return $this->configDto->getServiceBrakeControlsCount() === 2;
    }

    /**
     * @return bool
     */
    public function hasThreeAxles()
    {
        return $this->configDto->getNumberOfAxles() === 3;
    }

    /**
     * @return bool
     */
    public function isParkingBrakeOnTwoAxles()
    {
        return $this->hasThreeAxles() && $this->configDto->getParkingBrakeNumberOfAxles() === 2;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function isRollerOrPlateType($type)
    {
        return in_array(
            $type,
            [
                BrakeTestTypeCode::ROLLER,
                BrakeTestTypeCode::PLATE,
            ]
        );
    }
}
