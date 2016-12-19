<?php

namespace DvsaCommonTest\Dto\BrakeTest;

use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;

/**
 * Tests for BrakeTestConfigurationClass3AndAboveDto
 */
class BrakeTestConfigurationClass3AndAboveDtoTest extends \PHPUnit_Framework_TestCase
{
    /** @var BrakeTestConfigurationClass3AndAboveDto */
    private $dto;

    public function setUp()
    {
        $this->dto = new BrakeTestConfigurationClass3AndAboveDto();
    }

    public function testServiceBrake1TestType()
    {
        $serviceBrake1TestType = BrakeTestTypeCode::ROLLER;

        $this->dto->setServiceBrake1TestType($serviceBrake1TestType);
        $returnedValue = $this->dto->getServiceBrake1TestType();

        $this->assertSame($serviceBrake1TestType, $returnedValue);
    }

    public function testServiceBrake2TestType()
    {
        $serviceBrake2TestType =  BrakeTestTypeCode::ROLLER;

        $this->dto->setServiceBrake2TestType($serviceBrake2TestType);
        $returnedValue = $this->dto->getServiceBrake2TestType();

        $this->assertSame($serviceBrake2TestType, $returnedValue);
    }

    public function testParkingBrakeTestType()
    {
        $parkingBrakeTestType =  BrakeTestTypeCode::ROLLER;

        $this->dto->setParkingBrakeTestType($parkingBrakeTestType);
        $returnedValue = $this->dto->getParkingBrakeTestType();

        $this->assertSame($parkingBrakeTestType, $returnedValue);
    }

    public function testWeightType()
    {
        $weightType = 'vsi';

        $this->dto->setWeightType($weightType);
        $returnedValue = $this->dto->getWeightType();

        $this->assertSame($weightType, $returnedValue);
    }

    public function testVehicleWeight()
    {
        $vehicleWeight = '1020';

        $this->dto->setVehicleWeight($vehicleWeight);
        $returnedValue = $this->dto->getVehicleWeight();

        $this->assertSame($vehicleWeight, $returnedValue);
    }

    public function testWeightIsUnladen()
    {
        $weightIsUnladen = true;

        $this->dto->setWeightIsUnladen($weightIsUnladen);
        $returnedValue = $this->dto->getWeightIsUnladen();

        $this->assertSame($weightIsUnladen, $returnedValue);
    }

    public function testServiceBrakeIsSingleLine()
    {
        $serviceBrakeIsSingleLine = false;

        $this->dto->setServiceBrakeIsSingleLine($serviceBrakeIsSingleLine);
        $returnedValue = $this->dto->getServiceBrakeIsSingleLine();

        $this->assertSame($serviceBrakeIsSingleLine, $returnedValue);
    }

    public function testIsCommercialVehicle()
    {
        $isCommercialVehicle = true;

        $this->dto->setIsCommercialVehicle($isCommercialVehicle);
        $returnedValue = $this->dto->getIsCommercialVehicle();

        $this->assertSame($isCommercialVehicle, $returnedValue);
    }

    public function testIsSingleInFront()
    {
        $isSingleInFront = false;

        $this->dto->setIsSingleInFront($isSingleInFront);
        $returnedValue = $this->dto->getIsSingleInFront();

        $this->assertSame($isSingleInFront, $returnedValue);
    }

    public function testIsParkingBrakeOnTwoWheels()
    {
        $isParkingBrakeOnTwoWheels = true;

        $this->dto->setIsParkingBrakeOnTwoWheels($isParkingBrakeOnTwoWheels);
        $returnedValue = $this->dto->getIsParkingBrakeOnTwoWheels();

        $this->assertSame($isParkingBrakeOnTwoWheels, $returnedValue);
    }

    public function testServiceBrakeControlsCount()
    {
        $serviceBrakeControlsCount = 1;

        $this->dto->setServiceBrakeControlsCount($serviceBrakeControlsCount);
        $returnedValue = $this->dto->getServiceBrakeControlsCount();

        $this->assertSame($serviceBrakeControlsCount, $returnedValue);
    }

    public function testNumberOfAxles()
    {
        $numberOfAxles = 2;

        $this->dto->setNumberOfAxles($numberOfAxles);
        $returnedValue = $this->dto->getNumberOfAxles();

        $this->assertSame($numberOfAxles, $returnedValue);
    }

    public function testParkingBrakeNumberOfAxles()
    {
        $parkingBrakeNumberOfAxles = 2;

        $this->dto->setParkingBrakeNumberOfAxles($parkingBrakeNumberOfAxles);
        $returnedValue = $this->dto->getParkingBrakeNumberOfAxles();

        $this->assertSame($parkingBrakeNumberOfAxles, $returnedValue);
    }

    public function testVehicleClass()
    {
        $vehicleClass = VehicleClassCode::CLASS_4;

        $this->dto->setVehicleClass($vehicleClass);
        $returnedValue = $this->dto->getVehicleClass();

        $this->assertSame($vehicleClass, $returnedValue);
    }
}
