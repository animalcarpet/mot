<?php

namespace DvsaMotTest\Model;

use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResult;
use Dvsa\Mot\ApiClient\Resource\Item\BrakeTestResultClass1And2;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass1And2Dto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\DtoHydrator;

/**
 * Data for brake test result class 1 and 2 view
 */
class BrakeTestResultClass1And2ViewModel
{
    const EFFICIENCY_ABOVE_30 = '30% and above';
    const EFFICIENCY_BELOW_25 = '25% and below';
    const EFFICIENCY_BETWEEN_30_AND_25 = 'Between 25% and 30%';

    const NAME_GRADIENT_CONTROL_2 = 'gradientControl2';
    const NAME_GRADIENT_CONTROL_1 = 'gradientControl1';

    const ID_CONTROL_1_EFFORT_FRONT = 'control1EffortFront';
    const ID_CONTROL_1_LOCK_FRONT = 'control1LockFront';
    const ID_CONTROL_1_EFFORT_REAR = 'control1EffortRear';
    const ID_CONTROL_1_LOCK_REAR = 'control1LockRear';
    const ID_CONTROL_1_EFFORT_SIDECAR = 'control1EffortSidecar';
    const ID_CONTROL_1_BRAKE_EFFICIENCY = 'control1BrakeEfficiency';

    const ID_CONTROL_2_EFFORT_FRONT = 'control2EffortFront';
    const ID_CONTROL_2_LOCK_FRONT = 'control2LockFront';
    const ID_CONTROL_2_EFFORT_REAR = 'control2EffortRear';
    const ID_CONTROL_2_LOCK_REAR = 'control2LockRear';
    const ID_CONTROL_2_EFFORT_SIDECAR = 'control2EffortSidecar';
    const ID_CONTROL_2_BRAKE_EFFICIENCY = 'control2BrakeEfficiency';

    private $effortFront1;
    private $lockFront1;
    private $effortRear1;
    private $lockRear1;
    private $effortSidecar1;
    private $efficiency1;
    private $gradient1;

    private $effortFront2;
    private $lockFront2;
    private $effortRear2;
    private $lockRear2;
    private $effortSidecar2;
    private $efficiency2;
    private $gradient2;

    /** @var BrakeTestConfigurationClass1And2Helper $brakeTestConfiguration */
    private $brakeTestConfiguration;

    /**
     * @param BrakeTestConfigurationClass1And2Dto $brakeTestConfigurationClass1And2Dto
     * @param BrakeTestResultClass1And2 $brakeTestResult
     * @param array|null                          $postData
     */
    public function __construct(BrakeTestConfigurationClass1And2Dto $brakeTestConfigurationClass1And2Dto,
                                BrakeTestResultClass1And2 $brakeTestResult = null, $postData)
    {
        if($brakeTestConfigurationClass1And2Dto !== null || !isEmpty($brakeTestConfigurationClass1And2Dto)){
            $this->brakeTestConfiguration = new BrakeTestConfigurationClass1And2Helper($brakeTestConfigurationClass1And2Dto);

            $this->effortFront1 = ArrayUtils::tryGet(
                $postData,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_FRONT
            );
            $this->lockFront1 = ArrayUtils::tryGet($postData, BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_LOCK_FRONT);
            $this->effortRear1 = ArrayUtils::tryGet(
                $postData,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_REAR
            );
            $this->lockRear1 = ArrayUtils::tryGet($postData, BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_LOCK_REAR);
            $this->effortSidecar1 = ArrayUtils::tryGet(
                $postData,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_SIDECAR
            );
            $this->efficiency1 = ArrayUtils::tryGet(
                $postData,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_BRAKE_EFFICIENCY
            );
            $this->gradient1 = ArrayUtils::tryGet($postData, BrakeTestResultClass1And2ViewModel::NAME_GRADIENT_CONTROL_1);

            $this->effortFront2 = ArrayUtils::tryGet(
                $postData,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_FRONT
            );
            $this->lockFront2 = ArrayUtils::tryGet($postData, BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_LOCK_FRONT);
            $this->effortRear2 = ArrayUtils::tryGet(
                $postData,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_REAR
            );
            $this->lockRear2 = ArrayUtils::tryGet($postData, BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_LOCK_REAR);
            $this->effortSidecar2 = ArrayUtils::tryGet(
                $postData,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_SIDECAR
            );
            $this->efficiency2 = ArrayUtils::tryGet(
                $postData,
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_BRAKE_EFFICIENCY
            );
            $this->gradient2 = ArrayUtils::tryGet($postData, BrakeTestResultClass1And2ViewModel::NAME_GRADIENT_CONTROL_2);

        } else {
            $this->effortFront1 = $brakeTestResult->getControl1EffortFront();
            $this->lockFront1 = $brakeTestResult->getControl1LockFront();
            $this->effortRear1 = $brakeTestResult->getControl1EffortRear();
            $this->lockRear1 = $brakeTestResult->getControl1LockRear();
            $this->effortSidecar1 = $brakeTestResult->getControl1EffortSidecar();
            $this->efficiency1 = $brakeTestResult->getControl1BrakeEfficiency();
            $this->gradient1 = $brakeTestResult->getGradientControl1BelowMinimum();

            $this->effortFront2 = $brakeTestResult->getControl2EffortFront();
            $this->lockFront2 = $brakeTestResult->getControl2LockFront();
            $this->effortRear2 = $brakeTestResult->getControl2EffortRear();
            $this->lockRear2 = $brakeTestResult->getControl2LockRear();
            $this->effortSidecar2 = $brakeTestResult->getControl2EffortSidecar();
            $this->efficiency2 = $brakeTestResult->getControl2BrakeEfficiency();
            $this->gradient2 = $brakeTestResult->getGradientControl2BelowMinimum();
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge(
            $this->getLocalArray(),
            $this->getLocks(),
            DtoHydrator::dtoToJson($this->brakeTestConfiguration->getConfigDto()),
            $this->getGradientLimits(),
            $this->getDecelerometerArray()
        );
    }

    private function getDecelerometerArray()
    {
        return $this->brakeTestConfiguration->isDecelerometerTypeTest() ? [
            self::ID_CONTROL_1_BRAKE_EFFICIENCY => (int)$this->efficiency1,
            self::ID_CONTROL_2_BRAKE_EFFICIENCY => (int)$this->efficiency2
        ] : [];
    }

    /**
     * @return array
     */
    private function getLocalArray()
    {
        return [
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_FRONT   => $this->getEffortFront1(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_REAR    => $this->getEffortRear1(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_EFFORT_SIDECAR => $this->getEffortSidecar1(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_FRONT   => $this->getEffortFront2(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_REAR    => $this->getEffortRear2(),
            BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_EFFORT_SIDECAR => $this->getEffortSidecar2(),
        ];
    }

    /**
     * @return array
     */
    private function getLocks()
    {
        $locks = [];

        if ($this->brakeTestConfiguration->locksApplicableToTestType()) {
            $locks = [
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_LOCK_FRONT => $this->getLockFront1(),
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_1_LOCK_REAR  => $this->getLockRear1(),
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_LOCK_FRONT => $this->getLockFront2(),
                BrakeTestResultClass1And2ViewModel::ID_CONTROL_2_LOCK_REAR  => $this->getLockRear2(),
            ];
        }

        return $locks;
    }

    /**
     * @return array
     */
    private function getGradientLimits()
    {
        $gradientResults = [];

        if ($this->brakeTestConfiguration->isGradientTypeTest()) {
            $gradientResults = [
                'gradientControl1AboveUpperMinimum' => false,
                'gradientControl2AboveUpperMinimum' => false,
                'gradientControl1BelowMinimum'      => false,
                'gradientControl2BelowMinimum'      => false,
            ];

            if (BrakeTestResultClass1And2ViewModel::EFFICIENCY_ABOVE_30 === $this->gradient1) {
                $gradientResults['gradientControl1AboveUpperMinimum'] = true;
            } elseif (BrakeTestResultClass1And2ViewModel::EFFICIENCY_BELOW_25 === $this->gradient1) {
                $gradientResults['gradientControl1BelowMinimum'] = true;
            }

            if (BrakeTestResultClass1And2ViewModel::EFFICIENCY_ABOVE_30 === $this->gradient2) {
                $gradientResults['gradientControl2AboveUpperMinimum'] = true;
            } elseif (BrakeTestResultClass1And2ViewModel::EFFICIENCY_BELOW_25 === $this->gradient2) {
                $gradientResults['gradientControl2BelowMinimum'] = true;
            }
        }

        return $gradientResults;
    }

    /**
     * @return int|null
     */
    public function getEffortFront1()
    {
        return is_numeric($this->effortFront1) ? intval($this->effortFront1) : null;
    }

    /**
     * @return int|null
     */
    public function getEffortFront2()
    {
        return is_numeric($this->effortFront2) ? intval($this->effortFront2) : null;
    }

    /**
     * @return int|null
     */
    public function getEffortRear1()
    {
        return is_numeric($this->effortRear1) ? intval($this->effortRear1) : null;
    }

    /**
     * @return int|null
     */
    public function getEffortRear2()
    {
        return is_numeric($this->effortRear2) ? intval($this->effortRear2) : null;
    }

    /**
     * @return int|null
     */
    public function getEffortSidecar1()
    {
        return is_numeric($this->effortSidecar1) ? intval($this->effortSidecar1) : null;
    }

    /**
     * @return int|null
     */
    public function getEffortSidecar2()
    {
        return is_numeric($this->effortSidecar2) ? intval($this->effortSidecar2) : null;
    }

    /**
     * @return bool
     */
    public function getLockFront1()
    {
        return (bool)($this->lockFront1);
    }

    /**
     * @return bool
     */
    public function getLockFront2()
    {
        return (bool)($this->lockFront2);
    }

    /**
     * @return bool
     */
    public function getLockRear1()
    {
        return (bool)($this->lockRear1);
    }

    /**
     * @return bool
     */
    public function getLockRear2()
    {
        return (bool)($this->lockRear2);
    }

    /**
     * @return mixed
     */
    public function getEfficiency1()
    {
        return $this->efficiency1;
    }

    /**
     * @return mixed
     */
    public function getEfficiency2()
    {
        return $this->efficiency2;
    }

    /**
     * @return \DvsaMotTest\Model\BrakeTestConfigurationClass1And2Helper
     */
    public function getBrakeTestConfiguration()
    {
        return $this->brakeTestConfiguration;
    }
}
