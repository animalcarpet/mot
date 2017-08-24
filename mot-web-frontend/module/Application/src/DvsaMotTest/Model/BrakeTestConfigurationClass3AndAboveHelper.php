<?php

namespace DvsaMotTest\Model;

use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Domain\BrakeTestTypeConfiguration;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationDtoInterface as ConfigDto;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Mapper\BrakeTestWeightSourceMapper;
use DvsaFeature\FeatureToggles;

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

    private $vehicleClass;

    /**
     * @var BrakeTestConfigurationClass3AndAboveDto
     */
    private $configDto;

    /**
     * @var FeatureToggles
     */
    private $featureToggles;

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

    public function isSelectedWeightType($weightType)
    {
        if(empty($this->featureToggles) || !$this->featureToggles->isEnabled(FeatureToggle::VEHICLE_WEIGHT_FROM_VEHICLE)) {
            return $weightType == $this->configDto->getWeightType();
        }

        if(empty($weightType) || empty($this->configDto->getWeightType())) {
            return false;
        }

        $brakeTestWeightSourceMapper = new BrakeTestWeightSourceMapper();

        $isCheckedWeightTypeOfficial = $brakeTestWeightSourceMapper->isOfficialWeightSource(
            $this->getVehicleClass(),
            $weightType
        );

        $isDtoWeightTypeOfficial = $brakeTestWeightSourceMapper->isOfficialWeightSource(
            $this->getVehicleClass(),
            $this->configDto->getWeightType()
        );

        return $weightType == $this->configDto->getWeightType() || ($isCheckedWeightTypeOfficial && $isDtoWeightTypeOfficial);
    }

    /**
     * @return string
     */
    public function getVehicleClass()
    {
        return $this->vehicleClass;
    }

    /**
     * @param string $vehicleClass
     */
    public function setVehicleClass($vehicleClass)
    {
        $this->vehicleClass = $vehicleClass;
    }

    /**
     * We inject feature toggles through setter, so we don't need to modify way this helper is constructed
     * @param FeatureToggles $featureToggles
     */
    public function setFeatureToggles($featureToggles)
    {
        $this->featureToggles = $featureToggles;
    }
}
