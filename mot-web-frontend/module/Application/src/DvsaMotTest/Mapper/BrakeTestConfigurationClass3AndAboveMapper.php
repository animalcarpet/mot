<?php

namespace DvsaMotTest\Mapper;

use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationDtoInterface;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;
use DvsaMotTest\Specification\OfficialWeightSourceForVehicle;

/**
 * Maps form data to BrakeTestConfigurationClass3AndAboveDto.
 */
class BrakeTestConfigurationClass3AndAboveMapper implements BrakeTestConfigurationMapperInterface
{
    const BRAKE_LINE_TYPE_SINGLE = 'single';
    const VEHICLE_PURPOSE_TYPE_COMMERCIAL = 'commercial';
    const LOCATION_FRONT = 'front';

    /** @var OfficialWeightSourceForVehicle */
    private $officialWeightSourceForVehicleSpec;

    /**
     * @param OfficialWeightSourceForVehicle $officialWeightSourceForVehicleSpec
     */
    public function __construct(OfficialWeightSourceForVehicle $officialWeightSourceForVehicleSpec )
    {
        $this->officialWeightSourceForVehicleSpec = $officialWeightSourceForVehicleSpec;
    }

    /**
     * @param array $data
     *x
     * @return BrakeTestConfigurationDtoInterface
     */
    public function mapToDto($data)
    {
        TypeCheck::assertArray($data);

        $dto = new BrakeTestConfigurationClass3AndAboveDto();

        $dto->setServiceBrake1TestType(ArrayUtils::tryGet($data, 'serviceBrake1TestType'));
        $dto->setServiceBrake2TestType($this->setServiceBrake2TestType($data));
        $dto->setParkingBrakeTestType(ArrayUtils::tryGet($data, 'parkingBrakeTestType'));
        $dto->setWeightType(ArrayUtils::tryGet($data, 'weightType'));

        $dto->setVehicleWeight(ArrayUtils::tryGet($data, 'vehicleWeight'));

        $dto->setWeightIsUnladen(ArrayUtils::tryGet($data, 'weightIsUnladen') === '1');
        $dto->setServiceBrakeIsSingleLine(ArrayUtils::tryGet($data, 'brakeLineType') === self::BRAKE_LINE_TYPE_SINGLE);
        $dto->setIsCommercialVehicle(
            ArrayUtils::tryGet($data, 'vehiclePurposeType') === self::VEHICLE_PURPOSE_TYPE_COMMERCIAL
        );
        $dto->setIsSingleInFront($this->isSingleWheelInFront($data));
        $dto->setIsParkingBrakeOnTwoWheels(intval(ArrayUtils::tryGet($data, 'parkingBrakeWheelsCount')) !== 1);
        $dto->setServiceBrakeControlsCount(intval(ArrayUtils::tryGet($data, 'serviceBrakeControlsCount')));
        $dto->setNumberOfAxles(intval(ArrayUtils::tryGet($data, 'numberOfAxles')));
        $dto->setParkingBrakeNumberOfAxles(intval(ArrayUtils::tryGet($data, 'parkingBrakeNumberOfAxles')));
        $dto->setVehicleClass(intval(ArrayUtils::tryGet($data, 'vehicleClass')));

        return $dto;
    }

    /**
     * @param MotTest $motTest
     * @param DvsaVehicle $vehicle
     *
     * @return BrakeTestConfigurationClass3AndAboveDto
     */
    public function mapToDefaultDto(MotTest $motTest, DvsaVehicle $vehicle = null)
    {
        $dto = new BrakeTestConfigurationClass3AndAboveDto();

        $dto->setServiceBrake1TestType(BrakeTestTypeCode::ROLLER);
        $dto->setServiceBrake2TestType(null);
        $dto->setParkingBrakeTestType(BrakeTestTypeCode::ROLLER);


        $dto->setWeightType(
            $vehicle->getWeightSource() != null ? $vehicle->getWeightSource()->getCode() : null
        );

        $dto->setWeightIsUnladen(false);
        $dto->setServiceBrakeIsSingleLine(false);
        $dto->setIsCommercialVehicle(false);
        $dto->setIsSingleInFront(true);
        $dto->setIsParkingBrakeOnTwoWheels(false);
        $dto->setServiceBrakeControlsCount(1);
        $dto->setNumberOfAxles(2);
        $dto->setParkingBrakeNumberOfAxles(1);

        $dto->setVehicleWeight($this->getDefaultVehicleWeight($vehicle));

        // the defaults for brake test type from VTS will be populated in controller (BrakeTestResultsController)
        // because MotTest response obj don't have access to VTS data as it was before with DTO

        return $dto;
    }

    private function isSingleWheelInFront($data)
    {
        //TODO FD: the existing logic needs refactored so it no longer relies on this 'boolean' sometimes being null
        if (ArrayUtils::tryGet($data, 'positionOfSingleWheel') === null) {
            return null;
        }

        return ArrayUtils::tryGet($data, 'positionOfSingleWheel') === self::LOCATION_FRONT;
    }

    private function setServiceBrake2TestType($data)
    {
        if (intval(ArrayUtils::tryGet($data, 'serviceBrakeControlsCount')) === 2) {
            return ArrayUtils::tryGet($data, 'serviceBrake1TestType');
        }

        return null;
    }

    /**
     * @param DvsaVehicle $vehicle
     *
     * @return int|string
     */
    private function getDefaultVehicleWeight(DvsaVehicle $vehicle = null)
    {
        return $this->getDefaultVehicleWeightFromVehicle($vehicle);
    }

    /**
     * Sets default vehicle weight from DvsaVehicle object if it contains
     * an official weightSource type for a given vehicle class.
     * Otherwise sets it to blank.
     *
     * @see OfficialWeightSourceForVehicle
     * @see BL-5219
     *
     * @param DvsaVehicle $vehicle
     * @return int|null|string
     */
    private function getDefaultVehicleWeightFromVehicle(DvsaVehicle $vehicle)
    {
        if($this->officialWeightSourceForVehicleSpec->isSatisfiedBy($vehicle)){
            return $vehicle->getWeight();
        }

        return null;
    }
}
