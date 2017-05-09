<?php

namespace DvsaMotApi\Mapper;

use DvsaEntities\Entity\BrakeTestResultClass12;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Repository\BrakeTestTypeRepository;

/**
 * Class BrakeTestResultClass12Mapper.
 */
class BrakeTestResultClass12Mapper
{
    /** @var BrakeTestTypeRepository */
    private $brakeTestTypeRepository;

    public function __construct(BrakeTestTypeRepository $brakeTestTypeRepository)
    {
        $this->brakeTestTypeRepository = $brakeTestTypeRepository;
    }

    /**
     * @param array $data
     *
     * @return BrakeTestResultClass12
     */
    public function mapToObject(array $data)
    {
        $brakeTestResult = new BrakeTestResultClass12();

        $brakeTestType = $this->brakeTestTypeRepository->getByCode(ArrayUtils::get($data, 'brakeTestType'));

        $brakeTestResult
            ->setBrakeTestType($brakeTestType)
            ->setVehicleWeightFront(ArrayUtils::tryGet($data, 'vehicleWeightFront'))
            ->setVehicleWeightRear(ArrayUtils::tryGet($data, 'vehicleWeightRear'))
            ->setSidecarWeight(ArrayUtils::tryGet($data, 'sidecarWeight'))

            ->setControl1EffortFront(ArrayUtils::tryGet($data, 'control1EffortFront'))
            ->setControl1EffortRear(ArrayUtils::tryGet($data, 'control1EffortRear'))
            ->setControl1EffortSidecar(ArrayUtils::tryGet($data, 'control1EffortSidecar'))
            ->setControl1LockFront(ArrayUtils::tryGet($data, 'control1LockFront'))
            ->setControl1LockRear(ArrayUtils::tryGet($data, 'control1LockRear'))
            ->setControl1BrakeEfficiency(ArrayUtils::tryGet($data, 'control1BrakeEfficiency'))
            ->setControl1EfficiencyPass(ArrayUtils::tryGet($data, 'control1EfficiencyPass'))
            ->setGradientControl1AboveUpperMinimum(ArrayUtils::tryGet($data, 'gradientControl1AboveUpperMinimum'))
            ->setGradientControl1BelowMinimum(ArrayUtils::tryGet($data, 'gradientControl1BelowMinimum'))

            ->setControl2EffortFront(ArrayUtils::tryGet($data, 'control2EffortFront'))
            ->setControl2EffortRear(ArrayUtils::tryGet($data, 'control2EffortRear'))
            ->setControl2EffortSidecar(ArrayUtils::tryGet($data, 'control2EffortSidecar'))
            ->setControl2LockFront(ArrayUtils::tryGet($data, 'control2LockFront'))
            ->setControl2LockRear(ArrayUtils::tryGet($data, 'control2LockRear'))
            ->setControl2BrakeEfficiency(ArrayUtils::tryGet($data, 'control2BrakeEfficiency'))
            ->setControl2EfficiencyPass(ArrayUtils::tryGet($data, 'control2EfficiencyPass'))
            ->setGradientControl2AboveUpperMinimum(ArrayUtils::tryGet($data, 'gradientControl2AboveUpperMinimum'))
            ->setGradientControl2BelowMinimum(ArrayUtils::tryGet($data, 'gradientControl2BelowMinimum'));

        if (isset($data['riderWeight']) && !empty($data['riderWeight'])) {
            $brakeTestResult->setRiderWeight($data['riderWeight']);
        }

        return $brakeTestResult;
    }
}
