<?php

namespace DvsaMotTest\Model;

use DvsaCommon\Dto\BrakeTest\BrakeTestConfigurationClass1And2Dto;
use DvsaCommon\Enum\BrakeTestTypeCode;

/**
 * Class BrakeTestConfigurationClass1And2Test.
 */
class BrakeTestConfigurationClass1And2Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider configurationTestCases
     */
    public function testBrakeTestConfiguration($testCase, $expectedResults)
    {
        $dto = $this->constructDto($testCase);
        $configuration = new BrakeTestConfigurationClass1And2Helper($dto);

        $this->assertSame($expectedResults['locksApplicable'], $configuration->locksApplicableToTestType());
        $this->assertSame($expectedResults['isDecelerometer'], $configuration->isDecelerometerTypeTest());
        $this->assertSame($expectedResults['isFloor'], $configuration->isFloorTypeTest());
        $this->assertSame($expectedResults['isGradient'], $configuration->isGradientTypeTest());
        $this->assertSame($expectedResults['requiresEfforts'], $configuration->requiresEffortsTypeTest());
        $this->assertSame($expectedResults['requiresWeight'], $configuration->requiresWeight());
        $this->assertSame($expectedResults['hasSidecar'], $configuration->isSidecarAttached());
        $this->assertSame($testCase['vehicleWeightFront'], $configuration->getVehicleWeightFront());
        $this->assertSame($testCase['vehicleWeightRear'], $configuration->getVehicleWeightRear());
        $this->assertSame($testCase['riderWeight'], $configuration->getRiderWeight());
        $this->assertSame($testCase['sidecarWeight'], $configuration->getSidecarWeight());
        $this->assertSame($testCase['brakeTestType'], $configuration->getBrakeTestType());
        $this->assertSame($dto, $configuration->getConfigDto());
    }

    public static function configurationTestCases()
    {
        return [
            [
                [
                    'brakeTestType' => BrakeTestTypeCode::ROLLER,
                    'vehicleWeightFront' => 100,
                    'vehicleWeightRear' => 50,
                    'riderWeight' => 80,
                    'isSidecarAttached' => true,
                    'sidecarWeight' => 40,
                ],
                [
                    'locksApplicable' => true,
                    'isDecelerometer' => false,
                    'isFloor' => false,
                    'isGradient' => false,
                    'requiresEfforts' => true,
                    'requiresWeight' => true,
                    'hasSidecar' => true,
                ],
            ],
            [
                [
                    'brakeTestType' => BrakeTestTypeCode::PLATE,
                    'vehicleWeightFront' => 100,
                    'vehicleWeightRear' => 50,
                    'riderWeight' => 80,
                    'isSidecarAttached' => false,
                    'sidecarWeight' => null,
                ],
                [
                    'locksApplicable' => true,
                    'isDecelerometer' => false,
                    'isFloor' => false,
                    'isGradient' => false,
                    'requiresEfforts' => true,
                    'requiresWeight' => true,
                    'hasSidecar' => false,
                ],
            ],
            [
                [
                    'brakeTestType' => BrakeTestTypeCode::FLOOR,
                    'vehicleWeightFront' => 111,
                    'vehicleWeightRear' => 530,
                    'riderWeight' => 850,
                    'isSidecarAttached' => true,
                    'sidecarWeight' => 50,
                ],
                [
                    'locksApplicable' => true,
                    'isDecelerometer' => false,
                    'isFloor' => true,
                    'isGradient' => false,
                    'requiresEfforts' => false,
                    'requiresWeight' => true,
                    'hasSidecar' => true,
                ],
            ],
            [
                [
                    'brakeTestType' => BrakeTestTypeCode::DECELEROMETER,
                    'vehicleWeightFront' => null,
                    'vehicleWeightRear' => null,
                    'riderWeight' => null,
                    'isSidecarAttached' => false,
                    'sidecarWeight' => null,
                ],
                [
                    'locksApplicable' => false,
                    'isDecelerometer' => true,
                    'isFloor' => false,
                    'isGradient' => false,
                    'requiresEfforts' => false,
                    'requiresWeight' => false,
                    'hasSidecar' => false,
                ],
            ],
            [
                [
                    'brakeTestType' => BrakeTestTypeCode::GRADIENT,
                    'vehicleWeightFront' => null,
                    'vehicleWeightRear' => null,
                    'riderWeight' => null,
                    'isSidecarAttached' => false,
                    'sidecarWeight' => null,
                ],
                [
                    'locksApplicable' => false,
                    'isDecelerometer' => false,
                    'isFloor' => false,
                    'isGradient' => true,
                    'requiresEfforts' => false,
                    'requiresWeight' => false,
                    'hasSidecar' => false,
                ],
            ],
        ];
    }

    /**
     * @param $testCase
     *
     * @return BrakeTestConfigurationClass1And2Dto
     */
    private function constructDto($testCase)
    {
        $dto = new BrakeTestConfigurationClass1And2Dto();
        $dto->setBrakeTestType($testCase['brakeTestType']);
        $dto->setVehicleWeightFront($testCase['vehicleWeightFront']);
        $dto->setVehicleWeightRear($testCase['vehicleWeightRear']);
        $dto->setRiderWeight($testCase['riderWeight']);
        $dto->setSidecarWeight($testCase['sidecarWeight']);
        $dto->setIsSidecarAttached($testCase['isSidecarAttached']);

        return $dto;
    }
}
