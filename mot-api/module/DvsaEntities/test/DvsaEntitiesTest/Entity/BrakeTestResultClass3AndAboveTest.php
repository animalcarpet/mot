<?php

namespace DvsaEntitiesTest\Entity;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;

/**
 * Class BrakeTestResultClass3AndAboveTest
 */
class BrakeTestResultClass3AndAboveTest extends \PHPUnit_Framework_TestCase
{
    public function testSetsPropertiesCorrectlyBrakeTestResultClass3()
    {
        $data = self::getTestData();
        $brakeTestResult = self::getTestBrakeTestResult();

        $this->assertEquals(
            $data['parkingBrakeEffortOffside'],
            $brakeTestResult->getParkingBrakeEffortOffside()
        );
        $this->assertEquals(
            $data['parkingBrakeEffortNearside'],
            $brakeTestResult->getParkingBrakeEffortNearside()
        );
        $this->assertEquals(
            $data['parkingBrakeLockOffside'],
            $brakeTestResult->getParkingBrakeLockOffside()
        );
        $this->assertEquals(
            $data['parkingBrakeLockNearside'],
            $brakeTestResult->getParkingBrakeLockNearside()
        );
        $this->assertEquals(
            $data['serviceBrakeIsSingleLine'],
            $brakeTestResult->getServiceBrakeIsSingleLine()
        );
        $this->assertEquals(
            $data['isCommercialVehicle'],
            $brakeTestResult->getIsCommercialVehicle()
        );

        $this->assertEquals(
            $data['vehicleWeight'],
            $brakeTestResult->getVehicleWeight()
        );

        $this->assertEquals(
            $data['serviceBrake1Efficiency'],
            $brakeTestResult->getServiceBrake1Efficiency()
        );
        $this->assertEquals(
            $data['serviceBrake2Efficiency'],
            $brakeTestResult->getServiceBrake2Efficiency()
        );
        $this->assertEquals(
            $data['parkingBrakeEfficiency'],
            $brakeTestResult->getParkingBrakeEfficiency()
        );

        $this->assertEquals(
            $data['serviceBrake1EfficiencyPass'],
            $brakeTestResult->getServiceBrake1EfficiencyPass()
        );
        $this->assertEquals(
            $data['serviceBrake2EfficiencyPass'],
            $brakeTestResult->getServiceBrake2EfficiencyPass()
        );
        $this->assertEquals(
            $data['parkingBrakeEfficiencyPass'],
            $brakeTestResult->getParkingBrakeEfficiencyPass()
        );
        $this->assertEquals(
            $data['generalPass'],
            $brakeTestResult->getGeneralPass()
        );
    }

    /**
     * @return \DvsaEntities\Entity\BrakeTestResultClass3AndAbove
     */
    public static function getTestBrakeTestResult()
    {
        $brakeTestResult = new BrakeTestResultClass3AndAbove();
        $data = self::getTestData();
        $brakeTestResult
            ->setServiceBrake1TestType(BrakeTestTypeFactory::type($data['serviceBrake1TestType']))
            ->setParkingBrakeTestType(BrakeTestTypeFactory::type($data['parkingBrakeTestType']))
            ->setParkingBrakeEffortOffside($data['parkingBrakeEffortOffside'])
            ->setParkingBrakeEffortNearside($data['parkingBrakeEffortNearside'])
            ->setParkingBrakeEffortSecondaryOffside($data['parkingBrakeEffortSecondaryOffside'])
            ->setParkingBrakeEffortSecondaryNearside($data['parkingBrakeEffortSecondaryNearside'])
            ->setParkingBrakeLockOffside($data['parkingBrakeLockOffside'])
            ->setParkingBrakeLockNearside($data['parkingBrakeLockNearside'])
            ->setParkingBrakeLockSecondaryOffside($data['parkingBrakeLockSecondaryOffside'])
            ->setParkingBrakeLockSecondaryNearside($data['parkingBrakeLockSecondaryNearside'])
            ->setServiceBrakeIsSingleLine($data['serviceBrakeIsSingleLine'])
            ->setIsCommercialVehicle($data['isCommercialVehicle'])
            ->setVehicleWeight($data['vehicleWeight'])
            ->setWeightType(WeightSourceFactory::type($data['weightType']))
            ->setWeightIsUnladen($data['weightIsUnladen'])
            ->setServiceBrake1Efficiency($data['serviceBrake1Efficiency'])
            ->setServiceBrake2Efficiency($data['serviceBrake2Efficiency'])
            ->setParkingBrakeEfficiency($data['parkingBrakeEfficiency'])
            ->setServiceBrake1EfficiencyPass($data['serviceBrake1EfficiencyPass'])
            ->setServiceBrake2EfficiencyPass($data['serviceBrake2EfficiencyPass'])
            ->setParkingBrakeEfficiencyPass($data['parkingBrakeEfficiencyPass'])
            ->setGeneralPass($data['generalPass']);

        return $brakeTestResult;
    }

    public static function getTestData()
    {
        return [
            'id'                          => 2,
            'serviceBrake1TestType'       => BrakeTestTypeCode::ROLLER,
            'parkingBrakeTestType'        => BrakeTestTypeCode::ROLLER,
            'parkingBrakeEffortOffside'   => 15,
            'parkingBrakeEffortNearside'  => 16,
            'parkingBrakeEffortSecondaryOffside'  => 17,
            'parkingBrakeEffortSecondaryNearside' => 18,
            'parkingBrakeLockOffside'     => true,
            'parkingBrakeLockNearside'    => false,
            'parkingBrakeLockSecondaryOffside'  => true,
            'parkingBrakeLockSecondaryNearside' => false,
            'serviceBrakeIsSingleLine'    => false,
            'isCommercialVehicle'         => false,
            'vehicleWeight'               => 2000,
            'weightType'                  => WeightSourceCode::PRESENTED,
            'weightIsUnladen'             => true,
            'serviceBrake1Efficiency'     => 51,
            'serviceBrake2Efficiency'     => 50,
            'parkingBrakeEfficiency'      => 52,
            'serviceBrake1EfficiencyPass' => true,
            'serviceBrake2EfficiencyPass' => false,
            'parkingBrakeEfficiencyPass'  => false,
            'generalPass'                 => false,
            'numberOfAxles'               => 2,
            'riderWeight'                 => null
        ];
    }
}
