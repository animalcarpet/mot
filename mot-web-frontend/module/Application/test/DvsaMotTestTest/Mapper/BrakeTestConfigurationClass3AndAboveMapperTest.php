<?php

namespace DvsaMotTestTest\Mapper;

use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass3AndAboveMapper;
use DvsaMotTest\Specification\OfficialWeightSourceForVehicle;
use DvsaMotTestTest\TestHelper\Fixture;
use PHPUnit_Framework_MockObject_Matcher_InvokedRecorder;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests for BrakeTestConfigurationClass3AndAboveMapper.
 */
class BrakeTestConfigurationClass3AndAboveMapperTest extends PHPUnit_Framework_TestCase
{
    const DEFAULT_VEHICLE_WEIGHT = '1111.0';

    /** @var BrakeTestConfigurationClass3AndAboveMapper */
    private $mapper;
    /** @var FeatureToggles|MockObject */
    private $featureToggles;
    /** @var OfficialWeightSourceForVehicle|MockObject */
    private $officialWeightSourceForVehicle;
    /** @var DvsaVehicle|MockObject*/
    private $dvsaVehicle;

    public function setup()
    {
        $this->featureToggles = XMock::of(FeatureToggles::class);
        $this->officialWeightSourceForVehicle = XMock::of(OfficialWeightSourceForVehicle::class);
        $this->mapper = new BrakeTestConfigurationClass3AndAboveMapper(
            $this->featureToggles,
            $this->officialWeightSourceForVehicle
        );
        $this->dvsaVehicle = XMock::of(DvsaVehicle::class);
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

    /**
     * @param $toggleValue
     * @param $toggleInvocations
     * @param $specValue
     * @param $specInvocations
     * @param $expectedVehicleWeight
     *
     * @dataProvider testMapToDefaultDto_withMotTestWithoutBrakeTestResultDP
     */
    public function testMapToDefaultDto_withMotTestWithoutBrakeTestResult(
        $toggleValue,
        $toggleInvocations,
        $specValue,
        $specInvocations,
        $expectedVehicleWeight
    )
    {
        $motTestData = Fixture::getMotTestDataVehicleClass4(true);
        $this->withFeatureToggle($toggleValue, $toggleInvocations);
        $this->withOfficialWeightSourceSpec($specValue, $specInvocations);
        $this->withDvsaVehicle(self::DEFAULT_VEHICLE_WEIGHT);
        /*
         * this is the case when a new MOT Test does not have brake test submitted
         * vehicle weight should be empty in the dto because there is no source of brake tests
         * to populate the field in DTO
         *
         * @see BrakeTestConfigurationClass3AndAboveMapper @ mapToDefaultDto()
         */
        $motTestData->brakeTestResult = null;

        $motTest = new MotTest($motTestData);
        $expected = $this->getDefaultDto()
                        ->setVehicleWeight($expectedVehicleWeight); // vehicle weight from previous mot test (see fixture file)

        $actual = $this->mapper->mapToDefaultDto($motTest, $this->dvsaVehicle);

        $this->assertEquals($expected, $actual);
    }

    public function testMapToDefaultDto_withMotTestWithoutBrakeTestResultDP()
    {
        return [
            // ftValue, ftIC, specValue, specIC, expectedWeight
            [true, 1, true, 1, self::DEFAULT_VEHICLE_WEIGHT],
            [true, 1, false, 1, ''],
            [false, 1, false, 0, self::DEFAULT_VEHICLE_WEIGHT],
            [false, 1, false, 0, self::DEFAULT_VEHICLE_WEIGHT],
        ];
    }

    public function testMapToDefaultDto_withMotTestContainingDefaultBreakTestValues()
    {
        $vehicleWeight = '3000';
        $motTestData = Fixture::getMotTestDataVehicleClass4(true);
        $motTestData->brakeTestResult->serviceBrake1TestType = BrakeTestTypeCode::ROLLER;
        $motTestData->brakeTestResult->parkingBrakeTestType = BrakeTestTypeCode::ROLLER;
        $motTestData->brakeTestResult->vehicleWeight = $vehicleWeight;

        $motTest = new MotTest($motTestData);
        $expected = $this->getDefaultDto()
            ->setVehicleWeight($vehicleWeight);

        $actual = $this->mapper->mapToDefaultDto($motTest);

        $this->assertEquals($expected, $actual);
    }

    public function testMapToDefaultDtoWithVehicleWeightDefault()
    {
        $vehicleWeight = '1800';
        $motTestData = Fixture::getMotTestDataVehicleClass4(true);
        $motTestData->brakeTestResult->vehicleWeight = $vehicleWeight;
        $motTestData->brakeTestResult->serviceBrake1TestType = BrakeTestTypeCode::ROLLER;
        $motTestData->brakeTestResult->parkingBrakeTestType = BrakeTestTypeCode::ROLLER;
        $motTestData->vehicleClass = VehicleClassCode::CLASS_3;

        $motTest = new MotTest($motTestData);
        $expected = $this->getDefaultDto()->setVehicleWeight($vehicleWeight);

        $actual = $this->mapper->mapToDefaultDto($motTest);

        $this->assertEquals($expected, $actual);
    }

    public function testShouldReturnWeightTypeDGWForVehicleClass7AndWeightGreaterThan0()
    {
        $vehicleWeight = 3000;

        $expected = $this->getDefaultDto()->setVehicleWeight($vehicleWeight)->setWeightType(WeightSourceCode::DGW);

        $motTestData = Fixture::getMotTestDataVehicleClass4(true);

        $motTestData->brakeTestResult->vehicleWeight = $vehicleWeight;
        $motTestData->brakeTestResult->serviceBrake1TestType = BrakeTestTypeCode::ROLLER;
        $motTestData->brakeTestResult->parkingBrakeTestType = BrakeTestTypeCode::ROLLER;
        $motTestData->brakeTestResult->weightType = WeightSourceCode::DGW;
        $motTestData->vehicleClass = VehicleClassCode::CLASS_7;

        $motTest = new MotTest($motTestData);

        $actual = $this->mapper->mapToDefaultDto($motTest);
        $this->assertEquals($expected, $actual);
    }

    public function testShouldReturnWeightTypeVSIForVehicleClass7AndWeightEqual0()
    {
        $vehicleWeight = 0;

        $expected = $this->getDefaultDto()
            ->setVehicleWeight('0')
            ->setWeightType(WeightSourceCode::VSI);

        $motTestData = Fixture::getMotTestDataVehicleClass4(true);

        $motTestData->brakeTestResult->vehicleWeight = $vehicleWeight;
        $motTestData->brakeTestResult->serviceBrake1TestType = BrakeTestTypeCode::ROLLER;
        $motTestData->brakeTestResult->parkingBrakeTestType = BrakeTestTypeCode::ROLLER;
        $motTestData->brakeTestResult->weightType = WeightSourceCode::VSI;
        $motTestData->vehicleClass = VehicleClassCode::CLASS_7;

        $motTest = new MotTest($motTestData);

        $actual = $this->mapper->mapToDefaultDto($motTest);
        $this->assertEquals($expected, $actual);
    }

    public function testShouldReturnWeightTypeVSIForVehicleClass7AndWeightEqualNull()
    {
        $vehicleWeight = null;

        $motTestData = Fixture::getMotTestDataVehicleClass4(true);

        $motTestData->brakeTestResult->vehicleWeight = $vehicleWeight;
        $motTestData->brakeTestResult->serviceBrake1TestType = BrakeTestTypeCode::ROLLER;
        $motTestData->brakeTestResult->parkingBrakeTestType = BrakeTestTypeCode::ROLLER;
        $motTestData->brakeTestResult->weightType = WeightSourceCode::VSI;
        $motTestData->vehicleClass = VehicleClassCode::CLASS_7;

        $motTest = new MotTest($motTestData);

        $expected = $this->getDefaultDto()
            ->setVehicleWeight(null)
            ->setWeightType(WeightSourceCode::VSI);

        $actual = $this->mapper->mapToDefaultDto($motTest);
        $this->assertEquals($expected, $actual);
    }

    public function testShouldReturnDefaultVSIWeightTypeFoClass4Vehicles()
    {
        $vehicleWeight = null;

        $motTestData = Fixture::getMotTestDataVehicleClass4(true);

        $motTestData->brakeTestResult->vehicleWeight = $vehicleWeight;
        $motTestData->brakeTestResult->serviceBrake1TestType = BrakeTestTypeCode::ROLLER;
        $motTestData->brakeTestResult->parkingBrakeTestType = BrakeTestTypeCode::ROLLER;
        $motTestData->brakeTestResult->weightType = WeightSourceCode::VSI;
        $motTestData->vehicleClass = VehicleClassCode::CLASS_4;

        $motTest = new MotTest($motTestData);

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
            'vehicleClass' => VehicleClassCode::CLASS_4,
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
     * This returns the default DTO generated by
     * BrakeTestConfigurationClass3AndAboveMapper @ mapToDefaultDto().
     *
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
     * @param $returnValue
     * @param int $invocationCount
     */
    private function withFeatureToggle($returnValue, $invocationCount = 1)
    {
        $this->featureToggles
            ->expects($this->convertInvocationCount($invocationCount))
            ->method('isEnabled')
            ->with(FeatureToggle::VEHICLE_WEIGHT_FROM_VEHICLE)
            ->willReturn($returnValue);
    }

    /**
     * @param $returnValue
     * @param int $invocationCount
     */
    private function withOfficialWeightSourceSpec($returnValue, $invocationCount = 1)
    {
        $this->officialWeightSourceForVehicle
            ->expects($this->convertInvocationCount($invocationCount))
            ->method('isSatisfiedBy')
            ->willReturn($returnValue);
    }

    /**
     * @param $count
     * @return PHPUnit_Framework_MockObject_Matcher_InvokedRecorder
     */
    private function convertInvocationCount($count)
    {
        switch((int)$count){
            case 0:
                return $this->never();
            case 1:
                return $this->once();
            case 2:
                return $this->exactly(2);
            default:
                return $this->any();
        }
    }

    private function withDvsaVehicle($returnValue)
    {
        $this->dvsaVehicle
            ->expects($this->any())
            ->method('getWeight')
            ->willReturn($returnValue);
    }
}
