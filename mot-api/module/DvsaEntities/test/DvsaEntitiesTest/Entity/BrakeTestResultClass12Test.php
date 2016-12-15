<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\BrakeTestResultClass12;

/**
 * Class BrakeTestResultClass12Test.
 */
class BrakeTestResultClass12Test extends \PHPUnit_Framework_TestCase
{
    public function testSetsPropertiesCorrectly()
    {
        $brakeTestResult = self::getTestBrakeTestResult()
            ->setGradientControl1BelowMinimum(true)
            ->setGradientControl2BelowMinimum(true);

        $this->assertEquals(11, $brakeTestResult->getControl1EffortFront());
        $this->assertEquals(12, $brakeTestResult->getControl1EffortRear());
        $this->assertEquals(13, $brakeTestResult->getControl1EffortSidecar());
        $this->assertEquals(14, $brakeTestResult->getControl2EffortFront());
        $this->assertEquals(15, $brakeTestResult->getControl2EffortRear());
        $this->assertEquals(16, $brakeTestResult->getControl2EffortSidecar());
        $this->assertEquals(17, $brakeTestResult->getVehicleWeightFront());
        $this->assertEquals(18, $brakeTestResult->getVehicleWeightRear());
        $this->assertEquals(19, $brakeTestResult->getRiderWeight());
        $this->assertEquals(20, $brakeTestResult->getSidecarWeight());
        $this->assertEquals(21, $brakeTestResult->getControl1EfficiencyPass());
        $this->assertEquals(22, $brakeTestResult->getControl2EfficiencyPass());
        $this->assertTrue($brakeTestResult->getGradientControl1BelowMinimum());
        $this->assertTrue($brakeTestResult->getGradientControl2BelowMinimum());
        $this->assertFalse($brakeTestResult->getControl1LockFront());
        $this->assertFalse($brakeTestResult->getControl1LockRear());
        $this->assertFalse($brakeTestResult->getControl2LockFront());
        $this->assertFalse($brakeTestResult->getControl2LockRear());
        $this->assertTrue($brakeTestResult->getControl1EfficiencyPass());
        $this->assertTrue($brakeTestResult->getControl2EfficiencyPass());
        $this->assertTrue($brakeTestResult->getGeneralPass());
    }

    /**
     * @return \DvsaEntities\Entity\BrakeTestResultClass12
     */
    public static function getTestBrakeTestResult()
    {
        $brakeTestResult = new BrakeTestResultClass12();

        return $brakeTestResult
            ->setBrakeTestType(BrakeTestTypeFactory::roller())
            ->setControl1EffortFront(11)
            ->setControl1EffortRear(12)
            ->setControl1EffortSidecar(13)
            ->setControl2EffortFront(14)
            ->setControl2EffortRear(15)
            ->setControl2EffortSidecar(16)
            ->setVehicleWeightFront(17)
            ->setVehicleWeightRear(18)
            ->setRiderWeight(19)
            ->setSidecarWeight(20)
            ->setControl1LockFront(false)
            ->setControl1LockRear(false)
            ->setControl2LockFront(false)
            ->setControl2LockRear(false)
            ->setControl1BrakeEfficiency(21)
            ->setControl2BrakeEfficiency(22)
            ->setControl1EfficiencyPass(true)
            ->setControl2EfficiencyPass(true)
            ->setGeneralPass(true);
    }

    public static function getTestBrakeTestResultData()
    {
        return [
            'brakeTestType' => 'roller-brake',
            'control1EffortFront' => 11,
            'control1EffortRear' => 12,
            'control1EffortSidecar' => 13,
            'control2EffortFront' => 14,
            'control2EffortRear' => 15,
            'control2EffortSidecar' => 16,
            'control1LockFront' => 'No',
            'control1LockRear' => 'No',
            'control2LockFront' => 'No',
            'control2LockRear' => 'No',
            'vehicleWeightFront' => 17,
            'vehicleWeightRear' => 18,
            'riderWeight' => 19,
            'sidecarWeight' => 20,
        ];
    }
}
