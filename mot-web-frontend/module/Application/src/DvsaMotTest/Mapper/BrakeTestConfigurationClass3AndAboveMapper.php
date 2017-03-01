<?php

namespace DvsaMotTest\Mapper;

use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResultClass3AndAbove;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationDtoInterface;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;

/**
 * Maps form data to BrakeTestConfigurationClass3AndAboveDto.
 */
class BrakeTestConfigurationClass3AndAboveMapper implements BrakeTestConfigurationMapperInterface
{
    const BRAKE_LINE_TYPE_SINGLE = 'single';
    const VEHICLE_PURPOSE_TYPE_COMMERCIAL = 'commercial';
    const LOCATION_FRONT = 'front';

    /**
     * @param array $data
     *
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
     *
     * @return BrakeTestConfigurationClass3AndAboveDto
     */
    public function mapToDefaultDto(MotTest $motTest)
    {
        $dto = new BrakeTestConfigurationClass3AndAboveDto();

        $dto->setServiceBrake1TestType(BrakeTestTypeCode::ROLLER);
        $dto->setServiceBrake2TestType(null);
        $dto->setParkingBrakeTestType(BrakeTestTypeCode::ROLLER);
        $dto->setWeightType($this->getVehicleWeightType($motTest));
        $dto->setWeightIsUnladen(false);
        $dto->setServiceBrakeIsSingleLine(false);
        $dto->setIsCommercialVehicle(false);
        $dto->setIsSingleInFront(true);
        $dto->setIsParkingBrakeOnTwoWheels(false);
        $dto->setServiceBrakeControlsCount(1);
        $dto->setNumberOfAxles(2);
        $dto->setParkingBrakeNumberOfAxles(1);
        $dto->setVehicleWeight($this->getDefaultVehicleWeight($motTest));

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
     * @param MotTest $motTest
     *
     * @return int|string
     */
    private function getDefaultVehicleWeight(MotTest $motTest)
    {
        $vehicleWeight = '';
        $vehicleClass = $motTest->getVehicleClass();
        $brakeTestResult = $motTest->getBrakeTestResult();

        if (VehicleClassGroup::isGroupA($vehicleClass)) {
            return $vehicleWeight;
        }

        if ($brakeTestResult !== null) {
            $brakeTestResultClass3AndAbove = new BrakeTestResultClass3AndAbove($motTest->getBrakeTestResult());
            $vehicleWeight = $brakeTestResultClass3AndAbove->getVehicleWeight();
        } else {
            $vehicleWeight = $motTest->getPreviousTestVehicleWight();
        }

        return $vehicleWeight;
    }

    /**
     * @param MotTest $motTest
     *
     * @return string
     */
    private function getVehicleWeightType(MotTest $motTest)
    {
        $vehicleClass = $motTest->getVehicleClass();

        if (is_null($vehicleClass)) {
            return WeightSourceCode::VSI;
        }

        $brakeResult = $motTest->getBrakeTestResult();

        if ($vehicleClass == VehicleClassCode::CLASS_7) {

            if (is_null($brakeResult)) {

                if (!empty($motTest->getPreviousTestVehicleWight())) {
                    return WeightSourceCode::DGW;
                }

            } else {

                $brakeResultObject = new BrakeTestResultClass3AndAbove($brakeResult);

                if (!empty($brakeResultObject->getVehicleWeight())) {
                    return WeightSourceCode::DGW;
                }

            }
        }

        return WeightSourceCode::VSI;
    }
}
