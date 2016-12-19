<?php

namespace DvsaMotTest\Model;

use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass3AndAboveDto;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;

/**
 * Class BrakeTestConfigurationClass3AndAboveTest
 *
 * @package DvsaMotTest\Model
 */
class BrakeTestConfigurationClass3AndAboveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider configurationTestCases
     */
    public function testBrakeTestConfiguration($testCase, $expectedResults)
    {
        $dto = $this->constructDto($testCase);
        $configuration = new BrakeTestConfigurationClass3AndAboveHelper($dto);

        $this->assertSame(
            $expectedResults['locksApplicableService'], $configuration->locksApplicableToFirstServiceBrake()
        );
        $this->assertSame($expectedResults['locksApplicableParking'], $configuration->locksApplicableToParkingBrake());
        $this->assertSame(
            $expectedResults['effortsApplicableService'], $configuration->effortsApplicableToFirstServiceBrake()
        );
        $this->assertSame($expectedResults['parkingGradient'], $configuration->isParkingBrakeGradientType());
        $this->assertSame($expectedResults['parkingRollerPlate'], $configuration->isParkingBrakeTypeRollerOrPlate());
        $this->assertSame($testCase['numberOfAxles'], $configuration->getNumberOfAxles());
        $this->assertSame($testCase['parkingBrakeNumberOfAxles'], $configuration->getParkingBrakeNumberOfAxles());
        $this->assertSame($testCase['serviceBrake1TestType'], $configuration->getServiceBrakeTestType());
        $this->assertSame($testCase['parkingBrakeTestType'], $configuration->getParkingBrakeTestType());
        $this->assertSame($testCase['weightType'], $configuration->getWeightType());
        $this->assertSame($testCase['vehicleWeight'], $configuration->getVehicleWeight());
        $this->assertSame($testCase['weightIsUnladen'], $configuration->getWeightIsUnladen());
        $this->assertSame($expectedResults['serviceBrakeLineType'], $configuration->getServiceBrakeLineType());
        $this->assertSame($expectedResults['vehiclePurposeType'], $configuration->getVehiclePurposeType());
        $this->assertSame($expectedResults['positionOfSingleWheel'], $configuration->getPositionOfSingleWheel());
        $this->assertSame($expectedResults['parkingBrakeWheelsCount'], $configuration->getParkingBrakeWheelsCount());
        $this->assertSame($testCase['serviceBrakeControlsCount'], $configuration->getServiceBrakeControlsCount());
        $this->assertSame($testCase['isParkingBrakeOnTwoWheels'], $configuration->isParkingBrakeOnTwoWheels());
        $this->assertSame($expectedResults['singleWheelInFront'], $configuration->isSingleWheelInFront());
        $this->assertSame($expectedResults['hasOneServiceBrake'], !$configuration->hasTwoServiceBrakes());
        $this->assertSame($expectedResults['hasThreeAxles'], $configuration->hasThreeAxles());
        $this->assertSame($expectedResults['isParkingBrakeOnTwoAxles'], $configuration->isParkingBrakeOnTwoAxles());
        $this->assertSame($dto, $configuration->getConfigDto());
    }

    public static function configurationTestCases()
    {
        return [
            [[ //input #0
               'serviceBrake1TestType'     => BrakeTestTypeCode::ROLLER,
               'serviceBrake2TestType'     => BrakeTestTypeCode::ROLLER,
               'parkingBrakeTestType'      => BrakeTestTypeCode::ROLLER,
               'weightType'                => WeightSourceCode::PRESENTED,
               'vehicleClass'              => VehicleClassCode::CLASS_4,
               'vehicleWeight'             => '1000',
               'serviceBrakeIsSingleLine'  => true,
               'numberOfAxles'             => 2,
               'parkingBrakeNumberOfAxles' => 1,
               'weightIsUnladen'           => true,
               'isParkingBrakeOnTwoWheels' => false,
               'serviceBrakeControlsCount' => 1,
               'isSingleWheelInFront'      => true,
               'isCommercialVehicle'       => false,
             ],
             [ //output #0
               'locksApplicableService'   => true,
               'locksApplicableParking'   => true,
               'effortsApplicableService' => true,
               'parkingGradient'          => false,
               'parkingRollerPlate'       => true,
               'weightRequired'           => true,
               'weightUnladen'            => true,
               'isSingleLine'             => true,
               'hasOneServiceBrake'       => true,
               'singleWheelInFront'       => true,
               'hasThreeAxles'            => false,
               'isParkingBrakeOnTwoAxles' => false,
               'positionOfSingleWheel'    => 'front',
               'serviceBrakeLineType'     => 'single',
               'vehiclePurposeType'       => 'personal',
               'parkingBrakeWheelsCount'   => 1,
             ]],
            [[ //input #1
               'serviceBrake1TestType'     => BrakeTestTypeCode::PLATE,
               'serviceBrake2TestType'     => BrakeTestTypeCode::PLATE,
               'parkingBrakeTestType'      => BrakeTestTypeCode::PLATE,
               'weightType'                => WeightSourceCode::PRESENTED,
               'vehicleClass'              => VehicleClassCode::CLASS_4,
               'vehicleWeight'             => '1000',
               'serviceBrakeIsSingleLine'  => false,
               'numberOfAxles'             => 2,
               'parkingBrakeNumberOfAxles' => 1,
               'weightIsUnladen'           => true,
               'isParkingBrakeOnTwoWheels' => true,
               'serviceBrakeControlsCount' => 2,
               'isSingleWheelInFront'      => false,
               'isCommercialVehicle'       => false,
             ],
             [ //output #1
               'locksApplicableService'   => true,
               'locksApplicableParking'   => true,
               'effortsApplicableService' => true,
               'parkingGradient'          => false,
               'parkingRollerPlate'       => true,
               'weightRequired'           => true,
               'weightUnladen'            => true,
               'isSingleLine'             => false,
               'hasOneServiceBrake'       => false,
               'singleWheelInFront'       => false,
               'hasThreeAxles'            => false,
               'isParkingBrakeOnTwoAxles' => false,
               'positionOfSingleWheel'    => 'rear',
               'serviceBrakeLineType'     => 'dual',
               'vehiclePurposeType'       => 'personal',
               'parkingBrakeWheelsCount'   => 2,
             ]],
            [[ //input #2
               'serviceBrake1TestType'     => BrakeTestTypeCode::DECELEROMETER,
               'serviceBrake2TestType'     => BrakeTestTypeCode::DECELEROMETER,
               'parkingBrakeTestType'      => BrakeTestTypeCode::GRADIENT,
               'vehicleClass'              => VehicleClassCode::CLASS_4,
               'weightType'                => 'dgw',
               'vehicleWeight'             => '1010',
               'serviceBrakeIsSingleLine'  => false,
               'numberOfAxles'             => 3,
               'parkingBrakeNumberOfAxles' => 2,
               'weightIsUnladen'           => false,
               'isParkingBrakeOnTwoWheels' => false,
               'serviceBrakeControlsCount' => 1,
               'isSingleWheelInFront'      => true,
               'isCommercialVehicle'       => true,
             ],
             [ //output #2
               'locksApplicableService'   => false,
               'locksApplicableParking'   => false,
               'effortsApplicableService' => false,
               'parkingGradient'          => true,
               'parkingRollerPlate'       => false,
               'weightRequired'           => false,
               'weightUnladen'            => false,
               'isSingleLine'             => false,
               'hasOneServiceBrake'       => true,
               'singleWheelInFront'       => true,
               'hasThreeAxles'            => true,
               'isParkingBrakeOnTwoAxles' => true,
               'positionOfSingleWheel'    => 'front',
               'serviceBrakeLineType'     => 'dual',
               'vehiclePurposeType'       => 'commercial',
               'parkingBrakeWheelsCount'   => 1,
             ]],
        ];
    }

    private function constructDto($testCase)
    {
        $dto = new BrakeTestConfigurationClass3AndAboveDto();

        $dto->setServiceBrake1TestType($testCase['serviceBrake1TestType']);
        $dto->setServiceBrake2TestType($testCase['serviceBrake2TestType']);
        $dto->setParkingBrakeTestType($testCase['parkingBrakeTestType']);
        $dto->setWeightType($testCase['weightType']);
        $dto->setVehicleClass($testCase['vehicleClass']);
        $dto->setVehicleWeight($testCase['vehicleWeight']);
        $dto->setWeightIsUnladen($testCase['weightIsUnladen']);
        $dto->setServiceBrakeIsSingleLine($testCase['serviceBrakeIsSingleLine']);
        $dto->setIsCommercialVehicle($testCase['isCommercialVehicle']);
        $dto->setIsSingleInFront($testCase['isSingleWheelInFront']);
        $dto->setIsParkingBrakeOnTwoWheels($testCase['isParkingBrakeOnTwoWheels']);
        $dto->setServiceBrakeControlsCount($testCase['serviceBrakeControlsCount']);
        $dto->setNumberOfAxles($testCase['numberOfAxles']);
        $dto->setParkingBrakeNumberOfAxles($testCase['parkingBrakeNumberOfAxles']);

        return $dto;
    }
}
