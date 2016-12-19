<?php

namespace DvsaCommonTest\Domain;

use DvsaCommon\Domain\BrakeTestTypeConfiguration;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use PHPUnit_Framework_TestCase as TestCase;

class BrakeTestTypeConfigurationTest extends TestCase
{
    public function dataProviderForAllVehicleClassesAndServiceIsAnyAndParkingIsRollerOrPlate()
    {
        $vehicleClassCodes = [
            VehicleClassCode::CLASS_3,
            VehicleClassCode::CLASS_4,
            VehicleClassCode::CLASS_5,
            VehicleClassCode::CLASS_7
        ];
        $serviceBrakeTestTypes = BrakeTestTypeCode::getAll();
        $parkingBrakeTestTypes = [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE];

        return $this->getDataForLocksAreApplicableToFirstServiceBrakeTests(
            $vehicleClassCodes,
            $serviceBrakeTestTypes,
            $parkingBrakeTestTypes
        );
    }

    /**
     * @dataProvider dataProviderForAllVehicleClassesAndServiceIsAnyAndParkingIsRollerOrPlate
     */
    public function testLocksAreAlwaysApplicableToFirstServiceBrakeIfParkingBrakeIsRollerOrPlate(
        $vehicleClassCode,
        $serviceBrake1TestType,
        $parkingBrakeTestType
    ) {
        $this->assertTrue(BrakeTestTypeConfiguration::areServiceBrakeLocksApplicable(
            $vehicleClassCode,
            $serviceBrake1TestType,
            $parkingBrakeTestType
        ));
    }

    public function dataProviderForVehicleClasses4Or7AndServiceIsRollerOrPlateAndParkingIsDecelerometerOrGradient()
    {
        $vehicleClassCodes = [VehicleClassCode::CLASS_4, VehicleClassCode::CLASS_7];
        $serviceBrakeTestTypes = [BrakeTestTypeCode::ROLLER, BrakeTestTypeCode::PLATE];
        $parkingBrakeTestTypes = [BrakeTestTypeCode::DECELEROMETER, BrakeTestTypeCode::GRADIENT];

        return $this->getDataForLocksAreApplicableToFirstServiceBrakeTests(
            $vehicleClassCodes,
            $serviceBrakeTestTypes,
            $parkingBrakeTestTypes
        );
    }

    /**
     * @dataProvider dataProviderForVehicleClasses4Or7AndServiceIsRollerOrPlateAndParkingIsDecelerometerOrGradient
     *
     * @param $vehicleClassCode
     * @param $serviceBrake1TestType
     * @param $parkingBrakeTestType
     */
    public function testLocksAreApplicableToFirstServiceBrakeForClass4And7WhenParkingAndServiceBrakesAreRollerOrPlate(
        $vehicleClassCode,
        $serviceBrake1TestType,
        $parkingBrakeTestType
    ) {
        $this->assertTrue(BrakeTestTypeConfiguration::areServiceBrakeLocksApplicable(
            $vehicleClassCode,
            $serviceBrake1TestType,
            $parkingBrakeTestType
        ));
    }

    public function dataProviderForAllVehicleClassesAndParkingIsDecelerometerOrGradientAndServiceIsFloor()
    {
        $vehicleClassCodes = [
            VehicleClassCode::CLASS_3,
            VehicleClassCode::CLASS_4,
            VehicleClassCode::CLASS_5,
            VehicleClassCode::CLASS_7
        ];
        $parkingBrakeTestTypes = [BrakeTestTypeCode::DECELEROMETER, BrakeTestTypeCode::GRADIENT];
        $serviceBrakeTestTypes = [BrakeTestTypeCode::FLOOR];

        return $this->getDataForLocksAreApplicableToFirstServiceBrakeTests(
            $vehicleClassCodes,
            $serviceBrakeTestTypes,
            $parkingBrakeTestTypes,
            true
        );
    }

    /**
     * @dataProvider dataProviderForAllVehicleClassesAndParkingIsDecelerometerOrGradientAndServiceIsFloor
     *
     * @param $vehicleClassCode
     * @param $serviceBrake1TestType
     * @param $parkingBrakeTestType
     */
    public function testLocksAreNotApplicableToFirstServiceBrakeWhenParkingIsDecelerometerOrGradientAndServiceIsFloor(
        $vehicleClassCode,
        $serviceBrake1TestType,
        $parkingBrakeTestType
    ) {
        $this->assertFalse(BrakeTestTypeConfiguration::areServiceBrakeLocksApplicable(
            $vehicleClassCode,
            $serviceBrake1TestType,
            $parkingBrakeTestType
        ));
    }

    public function dataProviderForVehicleClass3AndParkingIsDecelerometerOrGradientAndServiceIsAny()
    {
        $vehicleClassCodes = [VehicleClassCode::CLASS_3];
        $parkingBrakeTestTypes = [BrakeTestTypeCode::DECELEROMETER, BrakeTestTypeCode::GRADIENT];
        $serviceBrakeTestTypes = BrakeTestTypeCode::getAll();

        return $this->getDataForLocksAreApplicableToFirstServiceBrakeTests(
            $vehicleClassCodes,
            $serviceBrakeTestTypes,
            $parkingBrakeTestTypes
        );
    }

    /**
     * @dataProvider dataProviderForVehicleClass3AndParkingIsDecelerometerOrGradientAndServiceIsAny
     *
     * @param $vehicleClassCode
     * @param $serviceBrake1TestType
     * @param $parkingBrakeTestType
     */
    public function testLocksAreNotApplicableToFirstServiceBrakeForClass3WhenBrakeTestTypeCombinationIsInvalid(
        $vehicleClassCode,
        $serviceBrake1TestType,
        $parkingBrakeTestType
    ) {
        $this->assertFalse(BrakeTestTypeConfiguration::areServiceBrakeLocksApplicable(
            $vehicleClassCode,
            $serviceBrake1TestType,
            $parkingBrakeTestType
        ));
    }

    public function dataProviderForVehicleClass5AndParkingIsDecelerometerOrGradientAndServiceIsPlate()
    {
        $vehicleClassCodes = [VehicleClassCode::CLASS_5];
        $parkingBrakeTestTypes = [BrakeTestTypeCode::DECELEROMETER, BrakeTestTypeCode::GRADIENT];
        $serviceBrakeTestTypes = [BrakeTestTypeCode::PLATE];

        return $this->getDataForLocksAreApplicableToFirstServiceBrakeTests(
            $vehicleClassCodes,
            $serviceBrakeTestTypes,
            $parkingBrakeTestTypes,
            true
        );
    }

    /**
     * @dataProvider dataProviderForVehicleClass5AndParkingIsDecelerometerOrGradientAndServiceIsPlate
     *
     * @param $vehicleClassCode
     * @param $serviceBrake1TestType
     * @param $parkingBrakeTestType
     */
    public function testLocksAreNotApplicableToFirstServiceBrakeForClass5WhenBrakeTestTypeCombinationIsInvalid(
        $vehicleClassCode,
        $serviceBrake1TestType,
        $parkingBrakeTestType
    ) {
        $this->assertFalse(BrakeTestTypeConfiguration::areServiceBrakeLocksApplicable(
            $vehicleClassCode,
            $serviceBrake1TestType,
            $parkingBrakeTestType
        ));
    }

    public function dataProviderForVehicleClass5AndParkingIsDecelerometerOrGradientAndServiceIsRoller()
    {
        $vehicleClassCodes = [VehicleClassCode::CLASS_5];
        $parkingBrakeTestTypes = [BrakeTestTypeCode::DECELEROMETER, BrakeTestTypeCode::GRADIENT];
        $serviceBrakeTestTypes = [BrakeTestTypeCode::ROLLER];

        return $this->getDataForLocksAreApplicableToFirstServiceBrakeTests(
            $vehicleClassCodes,
            $serviceBrakeTestTypes,
            $parkingBrakeTestTypes
        );
    }

    /**
     * @dataProvider dataProviderForVehicleClass5AndParkingIsDecelerometerOrGradientAndServiceIsRoller
     *
     * @param $vehicleClassCode
     * @param $serviceBrake1TestType
     * @param $parkingBrakeTestType
     */
    public function testLocksAreApplicableToFirstServiceBrakeForClass5WhenParkingIsDecelerometerOrGradientAndServiceIsRoller(
        $vehicleClassCode,
        $serviceBrake1TestType,
        $parkingBrakeTestType
    ) {
        $this->assertTrue(BrakeTestTypeConfiguration::areServiceBrakeLocksApplicable(
            $vehicleClassCode,
            $serviceBrake1TestType,
            $parkingBrakeTestType
        ));
    }

    /**
     * @param array $vehicleClassCodes
     * @param array $serviceBrakeTestTypes
     * @param array $parkingBrakeTestTypes
     * @param bool $includeInvalidBrakeTestCombinations
     * @return array
     */
    private function getDataForLocksAreApplicableToFirstServiceBrakeTests(
        array $vehicleClassCodes,
        array $serviceBrakeTestTypes,
        array $parkingBrakeTestTypes,
        $includeInvalidBrakeTestCombinations = false
    ) {
        $combinations = [];

        foreach ($vehicleClassCodes as $vehicleClassCode) {
            foreach ($serviceBrakeTestTypes as $serviceBrakeTestType) {
                foreach ($parkingBrakeTestTypes as $parkingBrakeTestType) {
                    $isValidBrakeTestCombination =
                        BrakeTestTypeConfiguration::isValid($vehicleClassCode, $serviceBrakeTestType, $parkingBrakeTestType);
                    if ($isValidBrakeTestCombination || $includeInvalidBrakeTestCombinations) {
                        $combinations[] = [$vehicleClassCode, $serviceBrakeTestType, $parkingBrakeTestType];
                    }
                }
            }
        }

        return $combinations;
    }
}
