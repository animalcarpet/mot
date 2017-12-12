<?php
declare(strict_types=1);

namespace DvsaMotApi\Service\Calculator;

use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;

class BrakeTestClass3AndAboveCalculationResult
{
    /**
     * @var ServiceBrakeCalculationResult
     */
    private $serviceBrakeCalculationResult1;

    /**
     * @var ServiceBrakeCalculationResult|null
     */
    private $serviceBrakeCalculationResult2;

    /**
     * @var ParkingBrakeCalculationResult
     */
    private $parkingBrakeCalculationResult;

    /**
     * @var BrakeTestResultClass3AndAbove
     */
    private $brakeTestResultClass3AndAbove;

    public function __construct(
        BrakeTestResultClass3AndAbove $brakeTestResultClass3AndAbove,
        ParkingBrakeCalculationResult $pbCalculationResult,
        ServiceBrakeCalculationResult $sbCalculationResult1,
        ServiceBrakeCalculationResult $sbCalculationResult2 = null
    )
    {
        $this->serviceBrakeCalculationResult1 = $sbCalculationResult1;
        $this->serviceBrakeCalculationResult2 = $sbCalculationResult2;
        $this->parkingBrakeCalculationResult = $pbCalculationResult;
        $this->brakeTestResultClass3AndAbove = $brakeTestResultClass3AndAbove;
    }

    /**
     * @return ServiceBrakeCalculationResult
     */
    public function getServiceBrakeCalculationResult1() : ServiceBrakeCalculationResult
    {
        return $this->serviceBrakeCalculationResult1;
    }

    /**
     * @return ServiceBrakeCalculationResult|null
     */
    public function getServiceBrakeCalculationResult2()
    {
        return $this->serviceBrakeCalculationResult2;
    }

    /**
     * @return ParkingBrakeCalculationResult
     */
    public function getParkingBrakeCalculationResult() : ParkingBrakeCalculationResult
    {
        return $this->parkingBrakeCalculationResult;
    }

    /**
     * @return BrakeTestResultClass3AndAbove
     */
    public function getBrakeTestResultClass3AndAbove() : BrakeTestResultClass3AndAbove
    {
        return $this->brakeTestResultClass3AndAbove;
    }
}