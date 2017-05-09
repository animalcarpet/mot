<?php

namespace DvsaMotApi\Mapper;

use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Entity\BrakeTestResultServiceBrakeData;
use DvsaEntities\Repository\BrakeTestTypeRepository;
use DvsaEntities\Repository\WeightSourceRepository;

/**
 * Class BrakeTestResultClass3AndAboveMapper.
 */
class BrakeTestResultClass3AndAboveMapper
{
    /** @var BrakeTestTypeRepository */
    private $brakeTestTypeRepository;

    /** @var WeightSourceRepository */
    private $weightSourceRepository;

    public function __construct(
        BrakeTestTypeRepository $brakeTestTypeRepository,
        WeightSourceRepository $weightSourceRepository
    ) {
        $this->brakeTestTypeRepository = $brakeTestTypeRepository;
        $this->weightSourceRepository = $weightSourceRepository;
    }

    /**
     * @param array $data
     *
     * @return BrakeTestResultClass3AndAbove
     */
    public function mapToObject(array $data)
    {
        $brakeTestResult = new BrakeTestResultClass3AndAbove();

        $parkingBrakeTestTypeEntity = $this->brakeTestTypeRepository
            ->getByCode(ArrayUtils::get($data, 'parkingBrakeTestType'));

        $serviceBrake1TestTypeEntity = $this->brakeTestTypeRepository
            ->getByCode(ArrayUtils::get($data, 'serviceBrake1TestType'));

        $serviceBrake2TestTypeText = ArrayUtils::tryGet($data, 'serviceBrake2TestType', null);
        $serviceBrake2TestTypeEntity = null;

        if (null !== $serviceBrake2TestTypeText) {
            $serviceBrake2TestTypeEntity = $this->brakeTestTypeRepository->getByCode($serviceBrake2TestTypeText);
        }

        $weightSourceText = ArrayUtils::tryGet($data, 'weightType', null);
        $weightSourceEntity = null;

        if (null !== $weightSourceText) {
            $weightSourceEntity = $this->weightSourceRepository
                ->getByCode(ArrayUtils::get($data, 'weightType'));
        }

        $brakeTestResult
            ->setServiceBrake1TestType($serviceBrake1TestTypeEntity)
            ->setServiceBrake1Efficiency(ArrayUtils::tryGet($data, 'serviceBrake1Efficiency'))
            ->setServiceBrake1EfficiencyPass(ArrayUtils::tryGet($data, 'serviceBrake1EfficiencyPass'))
            ->setServiceBrake2TestType($serviceBrake2TestTypeEntity)
            ->setServiceBrake2Efficiency(ArrayUtils::tryGet($data, 'serviceBrake2Efficiency'))
            ->setServiceBrake2EfficiencyPass(ArrayUtils::tryGet($data, 'serviceBrake2EfficiencyPass'))
            ->setParkingBrakeTestType($parkingBrakeTestTypeEntity)
            ->setParkingBrakeEfficiency(ArrayUtils::tryGet($data, 'parkingBrakeEfficiency'))
            ->setParkingBrakeEfficiencyPass(ArrayUtils::tryGet($data, 'parkingBrakeEfficiencyPass'))

            ->setParkingBrakeEffortNearside(ArrayUtils::tryGet($data, 'parkingBrakeEffortNearside'))
            ->setParkingBrakeEffortOffside(ArrayUtils::tryGet($data, 'parkingBrakeEffortOffside'))
            ->setParkingBrakeEffortSecondaryNearside(ArrayUtils::tryGet($data, 'parkingBrakeEffortSecondaryNearside'))
            ->setParkingBrakeEffortSecondaryOffside(ArrayUtils::tryGet($data, 'parkingBrakeEffortSecondaryOffside'))
            ->setParkingBrakeEffortSingle(ArrayUtils::tryGet($data, 'parkingBrakeEffortSingle'))

            ->setParkingBrakeLockNearside(ArrayUtils::tryGet($data, 'parkingBrakeLockNearside'))
            ->setParkingBrakeLockOffside(ArrayUtils::tryGet($data, 'parkingBrakeLockOffside'))
            ->setParkingBrakeLockSecondaryNearside(ArrayUtils::tryGet($data, 'parkingBrakeLockSecondaryNearside'))
            ->setParkingBrakeLockSecondaryOffside(ArrayUtils::tryGet($data, 'parkingBrakeLockSecondaryOffside'))
            ->setParkingBrakeLockSingle(ArrayUtils::tryGet($data, 'parkingBrakeLockSingle'))

            ->setIsSingleInFront(ArrayUtils::tryGet($data, 'isSingleInFront'))
            ->setServiceBrakeIsSingleLine(ArrayUtils::tryGet($data, 'serviceBrakeIsSingleLine', false))
            ->setIsCommercialVehicle(ArrayUtils::tryGet($data, 'isCommercialVehicle'))

            ->setVehicleWeight(ArrayUtils::tryGet($data, 'vehicleWeight'))
            ->setWeightType($weightSourceEntity)
            ->setWeightIsUnladen(ArrayUtils::tryGet($data, 'weightIsUnladen'))
            ->setNumberOfAxles(ArrayUtils::tryGet($data, 'numberOfAxles'))
            ->setParkingBrakeNumberOfAxles(ArrayUtils::tryGet($data, 'parkingBrakeNumberOfAxles'));

        if (!empty($data['serviceBrake1Data'])) {
            $brakeTestResult->setServiceBrake1Data($this->mapServiceBrakeDataToObject($data['serviceBrake1Data']));
        }
        if (!empty($data['serviceBrake2Data'])) {
            $brakeTestResult->setServiceBrake2Data($this->mapServiceBrakeDataToObject($data['serviceBrake2Data']));
        }

        return $brakeTestResult;
    }

    /**
     * @param array $data
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function mapServiceBrakeDataToObject(array $data)
    {
        $serviceBrakeData = new BrakeTestResultServiceBrakeData();
        $serviceBrakeData
            ->setEffortNearsideAxle1(ArrayUtils::tryGet($data, 'effortNearsideAxle1'))
            ->setEffortOffsideAxle1(ArrayUtils::tryGet($data, 'effortOffsideAxle1'))
            ->setEffortNearsideAxle2(ArrayUtils::tryGet($data, 'effortNearsideAxle2'))
            ->setEffortOffsideAxle2(ArrayUtils::tryGet($data, 'effortOffsideAxle2'))
            ->setEffortNearsideAxle3(ArrayUtils::tryGet($data, 'effortNearsideAxle3'))
            ->setEffortOffsideAxle3(ArrayUtils::tryGet($data, 'effortOffsideAxle3'))
            ->setEffortSingle(ArrayUtils::tryGet($data, 'effortSingle'))

            ->setLockNearsideAxle1(ArrayUtils::tryGet($data, 'lockNearsideAxle1'))
            ->setLockOffsideAxle1(ArrayUtils::tryGet($data, 'lockOffsideAxle1'))
            ->setLockNearsideAxle2(ArrayUtils::tryGet($data, 'lockNearsideAxle2'))
            ->setLockOffsideAxle2(ArrayUtils::tryGet($data, 'lockOffsideAxle2'))
            ->setLockNearsideAxle3(ArrayUtils::tryGet($data, 'lockNearsideAxle3'))
            ->setLockOffsideAxle3(ArrayUtils::tryGet($data, 'lockOffsideAxle3'))
            ->setLockSingle(ArrayUtils::tryGet($data, 'lockSingle'));

        return $serviceBrakeData;
    }
}
