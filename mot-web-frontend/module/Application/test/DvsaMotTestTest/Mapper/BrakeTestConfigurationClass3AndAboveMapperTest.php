<?php

namespace DvsaMotTestTest\Mapper;

use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass3AndAboveMapper;
use PHPUnit_Framework_TestCase;

/**
 * Tests for BrakeTestConfigurationClass3AndAboveMapper.
 */
class BrakeTestConfigurationClass3AndAboveMapperTest extends PHPUnit_Framework_TestCase
{
    /** @var BrakeTestConfigurationClass3AndAboveMapper */
    private $mapper;

    public function setup()
    {
        $this->mapper = new BrakeTestConfigurationClass3AndAboveMapper();
    }

    public function testMapToDto()
    {
        $testData = $this->getStandardMapInputData();
        $expected = $this->getStandardMapOutputDto();

        $actual = $this->mapper->mapToDto($testData);

        $this->assertEquals($expected, $actual);
    }

    public function testMapToDtoWith2ServiceBrakeControls()
    {
        $testData = $this->getStandardMapInputData();
        $testData['serviceBrakeControlsCount'] = '2';

        $expected = $this->getStandardMapOutputDto()
            ->setServiceBrake2TestType(BrakeTestTypeCode::ROLLER)
            ->setServiceBrakeControlsCount(2);

        $actual = $this->mapper->mapToDto($testData);

        $this->assertEquals($expected, $actual);
    }

    public function testMapToDtoWithInvalidInput()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->mapper->mapToDto(null);
    }

    public function testMapToDefaultDto()
    {
        $motTest = new MotTestDto();
        $expected = $this->getDefaultDto();

        $actual = $this->mapper->mapToDefaultDto($motTest);

        $this->assertEquals($expected, $actual);
    }

    public function testMapToDefaultDtoWithVehicleWeightDefault()
    {
        $vehicleWeight = 1800;
        $motTest = $this->getMotTestWithPresentWeightAndPresentVehicleClassCode($vehicleWeight, VehicleClassCode::CLASS_4);
        $expected = $this->getDefaultDto()->setVehicleWeight($vehicleWeight);

        $actual = $this->mapper->mapToDefaultDto($motTest);

        $this->assertEquals($expected, $actual);
    }

    public function testMapToDefaultDtoWithSiteDefault()
    {
        $motTest = (new MotTestDto())->setVehicleTestingStation(
            [
                'defaultParkingBrakeTestClass3AndAbove' => BrakeTestTypeCode::GRADIENT,
                'defaultServiceBrakeTestClass3AndAbove' => BrakeTestTypeCode::DECELEROMETER,
            ]
        );
        $expected = $this->getDefaultDto()
            ->setServiceBrake1TestType(BrakeTestTypeCode::DECELEROMETER)
            ->setParkingBrakeTestType(BrakeTestTypeCode::GRADIENT);

        $actual = $this->mapper->mapToDefaultDto($motTest);

        $this->assertEquals($expected, $actual);
    }

    public function testShouldReturnWeightTypeDGWForVehicleClass7AndWeightGreaterThan0()
    {
        $vehicleWeight = 3000;
        $motTest = $this->getMotTestWithPresentWeightAndPresentVehicleClassCode($vehicleWeight);

        $expected = $this->getDefaultDto()
            ->setVehicleWeight('3000')
            ->setWeightType(WeightSourceCode::DGW);

        $actual = $this->mapper->mapToDefaultDto($motTest);
        $this->assertEquals($expected, $actual);
    }

    public function testShouldReturnWeightTypeVSIForVehicleClass7AndWeightEqual0()
    {
        $vehicleWeight = 0;
        $motTest = $this->getMotTestWithPresentWeightAndPresentVehicleClassCode($vehicleWeight);

        $expected = $this->getDefaultDto()
            ->setVehicleWeight('0')
            ->setWeightType(WeightSourceCode::VSI);

        $actual = $this->mapper->mapToDefaultDto($motTest);
        $this->assertEquals($expected, $actual);
    }

    public function testShouldReturnWeightTypeVSIForVehicleClass7AndWeightEqualNull()
    {
        $vehicleWeight = null;
        $motTest = $this->getMotTestWithPresentWeightAndPresentVehicleClassCode($vehicleWeight);

        $expected = $this->getDefaultDto()
            ->setVehicleWeight(null)
            ->setWeightType(WeightSourceCode::VSI);

        $actual = $this->mapper->mapToDefaultDto($motTest);
        $this->assertEquals($expected, $actual);
    }

    public function testShouldReturnDefaultVSIWeightTypeFoClass4Vehicles()
    {
        $vehicleWeight = '';
        $motTest = $this->getMotTestWithPresentWeightAndPresentVehicleClassCode($vehicleWeight, VehicleClassCode::CLASS_4);

        $expected = $this->getDefaultDto()->setWeightType(WeightSourceCode::VSI);

        $actual = $this->mapper->mapToDefaultDto($motTest);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    private function getStandardMapInputData()
    {
        return [
            'serviceBrake1TestType' => BrakeTestTypeCode::ROLLER,
            'positionOfSingleWheel' => BrakeTestConfigurationClass3AndAboveMapper::LOCATION_FRONT,
            'parkingBrakeTestType' => BrakeTestTypeCode::ROLLER,
            'weightType' => WeightSourceCode::VSI,
            'vehicleWeight' => '1400',
            'weightIsUnladen' => '1',
            'brakeLineType' => BrakeTestConfigurationClass3AndAboveMapper::BRAKE_LINE_TYPE_SINGLE,
            'vehiclePurposeType' => BrakeTestConfigurationClass3AndAboveMapper::VEHICLE_PURPOSE_TYPE_COMMERCIAL,
            'parkingBrakeWheelsCount' => '1',
            'serviceBrakeControlsCount' => '1',
            'numberOfAxles' => '2',
            'parkingBrakeNumberOfAxles' => '1',
            'vehicleClass' => VehicleClassCode::CLASS_4
        ];
    }

    /**
     * @return BrakeTestConfigurationClass3AndAboveDto
     */
    private function getStandardMapOutputDto()
    {
        return (new BrakeTestConfigurationClass3AndAboveDto())
            ->setServiceBrake1TestType(BrakeTestTypeCode::ROLLER)
            ->setServiceBrake2TestType(null)
            ->setParkingBrakeTestType(BrakeTestTypeCode::ROLLER)
            ->setWeightType(WeightSourceCode::VSI)
            ->setVehicleWeight('1400')
            ->setWeightIsUnladen(true)
            ->setServiceBrakeIsSingleLine(true)
            ->setIsCommercialVehicle(true)
            ->setIsSingleInFront(true)
            ->setIsParkingBrakeOnTwoWheels(false)
            ->setServiceBrakeControlsCount(1)
            ->setNumberOfAxles(2)
            ->setParkingBrakeNumberOfAxles(1)
            ->setVehicleClass(VehicleClassCode::CLASS_4);
    }

    /**
     * @return BrakeTestConfigurationClass3AndAboveDto
     */
    private function getDefaultDto()
    {
        return (new BrakeTestConfigurationClass3AndAboveDto())
            ->setServiceBrake1TestType(BrakeTestTypeCode::ROLLER)
            ->setServiceBrake2TestType(null)
            ->setParkingBrakeTestType(BrakeTestTypeCode::ROLLER)
            ->setWeightType(WeightSourceCode::VSI)
            ->setWeightIsUnladen(false)
            ->setServiceBrakeIsSingleLine(false)
            ->setIsCommercialVehicle(false)
            ->setIsSingleInFront(true)
            ->setIsParkingBrakeOnTwoWheels(false)
            ->setServiceBrakeControlsCount(1)
            ->setNumberOfAxles(2)
            ->setParkingBrakeNumberOfAxles(1)
            ->setVehicleWeight('');
    }

    /**
     * @param $vehicleWeight
     * @param $vehicleClassCode
     *
     * @return MotTestDto
     */
    private function getMotTestWithPresentWeightAndPresentVehicleClassCode($vehicleWeight, $vehicleClassCode = VehicleClassCode::CLASS_7)
    {
        $vehicleClass = (new VehicleClassDto())->setCode($vehicleClassCode);
        $vehicle = (new VehicleDto())->setVehicleClass($vehicleClass)->setWeight($vehicleWeight);
        $motTestDto = (new MotTestDto())->setVehicle($vehicle);

        return $motTestDto;
    }
}
