<?php

namespace DvsaMotTest\Mapper;

use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResultClass3AndAbove;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationDtoInterface;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
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
     * @param string $vehicleClass
     *
     * @return BrakeTestConfigurationDtoInterface
     */
    public function mapToDefaultDto(MotTest $motTest, $vehicleClass = null)
    {
        $dto = new BrakeTestConfigurationClass3AndAboveDto();

        $dto->setServiceBrake1TestType(BrakeTestTypeCode::ROLLER);
        $dto->setServiceBrake2TestType(null);
        $dto->setParkingBrakeTestType(BrakeTestTypeCode::ROLLER);
        $dto->setWeightType($this->getVehicleWeightType($motTest, $vehicleClass));
        $dto->setWeightIsUnladen(false);
        $dto->setServiceBrakeIsSingleLine(false);
        $dto->setIsCommercialVehicle(false);
        $dto->setIsSingleInFront(true);
        $dto->setIsParkingBrakeOnTwoWheels(false);
        $dto->setServiceBrakeControlsCount(1);
        $dto->setNumberOfAxles(2);
        $dto->setParkingBrakeNumberOfAxles(1);
        $dto->setVehicleWeight($this->getDefaultVehicleWeight($motTest, $vehicleClass));

        //@TODO Question over this!
        if ($motTest->getBrakeTestResult() !== null) {
            $brakeTestResult = new BrakeTestResultClass3AndAbove($motTest->getBrakeTestResult());
            $dto->setParkingBrakeTestType(
                $brakeTestResult->getParkingBrakeTestType()
            );
            $dto->setServiceBrake1TestType(
                $brakeTestResult->getServiceBrake1TestType()
            );
        }

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
     * @param string $vehicleClass
     *
     * @return int|string
     */
    private function getDefaultVehicleWeight(MotTest $motTest, $vehicleClass)
    {
        $vehicleWeight = '';
        $brakeTestResult = $motTest->getBrakeTestResult();
        if($brakeTestResult !== null) {
            $brakeTestResultClass3AndAbove = new BrakeTestResultClass3AndAbove($motTest->getBrakeTestResult());

            if (in_array($vehicleClass, VehicleClassCode::getGroupBClasses())) {
                $vehicleWeight = $brakeTestResultClass3AndAbove->getVehicleWeight();
            }
        }

        return $vehicleWeight;
    }

    /**
     * @param MotTest $motTest
     * @param string $vehicleClass
     *
     * @return string
     */
    private function getVehicleWeightType(MotTest $motTest, $vehicleClass)
    {
        if (!is_null($vehicleClass)) {
            return WeightSourceCode::VSI;
        }

        $brakeResult = $motTest->getBrakeTestResult();

        if(!is_null($brakeResult)){
            $brakeResultObject = new BrakeTestResultClass3AndAbove($brakeResult);
            if ($vehicleClass == VehicleClassCode::CLASS_7 && !empty($brakeResultObject->getVehicleWeight())) {
                return WeightSourceCode::DGW;
            }
        }

        return WeightSourceCode::VSI;
    }
}
