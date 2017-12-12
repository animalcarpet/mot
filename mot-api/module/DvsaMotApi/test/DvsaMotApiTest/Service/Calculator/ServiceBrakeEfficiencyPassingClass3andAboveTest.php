<?php

namespace DvsaMotApiTest\Service\Calculator;

use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\BrakeTestResultServiceBrakeData;
use DvsaEntities\Entity\BrakeTestType;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculator;
use DvsaMotApi\Service\Calculator\CalculationFailureSeverity;
use DvsaMotApi\Service\Calculator\ServiceBrakeCalculationResult;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class ServiceBrakeEfficiencyPassingClass3andAboveTest extends \PHPUnit_Framework_TestCase
{
    const SERVICE_BRAKE_1 = 1;
    const SERVICE_BRAKE_2 = 2;
    const DEFAULT_DATE = '2017-01-01';
    const AXLE_1 = 1;
    const AXLE_2 = 2;
    const AXLE_3 = 3;
    const NEARSIDE = 'Nearside';
    const OFFSIDE = 'Offside';

    /** @var BrakeTestResultClass3AndAbove  */
    private $brakeTestResultClass3AndAbove;
    /** @var Vehicle */
    private $vehicle;
    /** @var FeatureToggles | MockObj */
    private $featureToggles;

    /** @var BrakeTestResultClass3AndAboveCalculator */
    private $sut;

    public function setUp()
    {
        $this->setUpVehicleDefaults();
        $this->setUpBrakeTestResultDefaults();

        $this->featureToggles = XMock::of(FeatureToggles::class);
        $this->sut = new BrakeTestResultClass3AndAboveCalculator(
            $this->featureToggles
        );
    }

    private function setUpVehicleDefaults()
    {
        $this->vehicle = new Vehicle();

        $modelDetail = new ModelDetail();
        $this->vehicle->setModelDetail($modelDetail);
    }

    private function setUpBrakeTestResultDefaults()
    {
        $this->brakeTestResultClass3AndAbove = new BrakeTestResultClass3AndAbove();

        // we don't test parking brake calculations here so it being defaulted to most popular value
        $this->setParkingBrakeTestType(BrakeTestTypeCode::DECELEROMETER);
    }

    /**
     * Tests if you can pass a service brake test based solely on exceeding minimum efficiency threshold.
     * No other rules are checked - e.g the minimum number of locks
     *
     * @dataProvider withSingleServiceBrake_itIsPassingByExceedingSingleThresholdAloneDP
     *
     * @param string $vehicleClassCode
     * @param string $serviceBrakeTypeCode
     * @param bool $isCommercialVehicle
     * @param \DateTime $dateOfFirstUse
     * @param int $serviceBrake1Efficiency
     * @param int $expectedMinimumThreshold - this is used just to generate human-readable error msg.
     */
    public function testCreateServiceBrakeCalculationResult_withSingleServiceBrake_itIsPassingByExceedingSingleThresholdAlone(
        $vehicleClassCode,
        $serviceBrakeTypeCode,
        $isCommercialVehicle,
        $dateOfFirstUse,
        $serviceBrake1Efficiency,
        $expectedMinimumThreshold
    )
    {
        $this->withVehicleOfClass($vehicleClassCode, $dateOfFirstUse);
        $this->withCommercialVehicle($isCommercialVehicle);
        $this->withSingleServiceBrakeDataProvided($serviceBrake1Efficiency);

        /** @var ServiceBrakeCalculationResult $calculationResult */
        $calculationResult = $this->sut->createServiceBrakeCalculationResult(
            $this->vehicle,
            $serviceBrakeTypeCode,
            $this->brakeTestResultClass3AndAbove,
            self::SERVICE_BRAKE_1
        );

        $failMsg = sprintf(
            "Test failed with %d efficiency - Minimum threshold to PASS is %d",
            $serviceBrake1Efficiency,
            $expectedMinimumThreshold
        );

        $this->assertTrue($serviceBrake1Efficiency >= $expectedMinimumThreshold, $failMsg);
        $this->assertTrue($calculationResult->isPassing(), $failMsg);
        $this->assertEquals(
            CalculationFailureSeverity::NONE,
            $calculationResult->getFailureSeverity(),
            "Calculation failure severity doesn't match the expected"
        );
    }

    public function withSingleServiceBrake_itIsPassingByExceedingSingleThresholdAloneDP()
    {
        return [
            // --------- CLASS 3:
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 50,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            // for old vehicles of class 3 there is lower efficiency threshold
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY, '-1 day'),
                'serviceBrake1Efficiency' => 40,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_3_PRE_1968
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 50,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 50,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            // --------- CLASS 4:
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '-1 day'),
                'serviceBrake1Efficiency' => 50,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            // for class 4 with fistDateOfUse >= '2010-09-01' there are different thresholds for commercial and non-commercial vehicles
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 58,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => true,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 50,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010_COMMERCIAL
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 58,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => true,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 50,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010_COMMERCIAL
            ],
            // --------- CLASS 5:
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_5,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 50,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            // --------- CLASS 7:
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 50,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],

        ];
    }

    /**
     * @dataProvider withSingleServiceBrake_itIsPassingByUsingLockRuleDP
     *
     * @param string $vehicleClassCode
     * @param string $serviceBrakeTypeCode
     * @param string $parkingBrakeTypeCode
     * @param bool $isCommercialVehicle
     * @param \DateTime $dateOfFirstUse
     * @param int $serviceBrake1Efficiency
     * @param int $wheelsLocked
     * @param int $expectedMinimumThreshold - this is used just to generate human-readable error msg.
     * @param int $expectedMinimumLockThreshold - this is used just to generate human-readable error msg.
     */
    public function testCreateServiceBrakeCalculationResult_withSingleServiceBrake_itIsPassingByUsingLockRule(
        $vehicleClassCode,
        $serviceBrakeTypeCode,
        $parkingBrakeTypeCode,
        $isCommercialVehicle,
        $dateOfFirstUse,
        $serviceBrake1Efficiency,
        $wheelsLocked,
        $expectedMinimumThreshold,
        $expectedMinimumLockThreshold
    )
    {
        $this->withVehicleOfClass($vehicleClassCode, $dateOfFirstUse);
        $this->withCommercialVehicle($isCommercialVehicle);
        $this->withSingleServiceBrakeDataProvided($serviceBrake1Efficiency);
        $this->withNumberOfWheelsLockedOnServiceBrakeOne($wheelsLocked);
        // a valid combination of service/parking brake test type has to be set in order to get the lock rule applicable
        $this->withParkingBrakeTestType($parkingBrakeTypeCode);

        /** @var ServiceBrakeCalculationResult $calculationResult */
        $calculationResult = $this->sut->createServiceBrakeCalculationResult(
            $this->vehicle,
            $serviceBrakeTypeCode,
            $this->brakeTestResultClass3AndAbove,
            self::SERVICE_BRAKE_1
        );

        $failMsg = sprintf(
            "The efficiency %d is below the minimum threshold (%d) but the test should pass because of the minimum lock wheels rule (actual: %d minimum: %d)",
            $serviceBrake1Efficiency,
            $expectedMinimumThreshold,
            $wheelsLocked,
            $expectedMinimumLockThreshold
        );

        $this->assertTrue(
            $serviceBrake1Efficiency < $expectedMinimumThreshold && $wheelsLocked >= $expectedMinimumLockThreshold,
            $failMsg
        );
        $this->assertTrue($calculationResult->isPassing(), $failMsg);
        $this->assertEquals(
            CalculationFailureSeverity::NONE,
            $calculationResult->getFailureSeverity(),
            "Calculation failure severity doesn't match the expected"
        );
    }

    public function withSingleServiceBrake_itIsPassingByUsingLockRuleDP()
    {
        return [
            // --------- CLASS 3:
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 2,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 3,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::PLATE,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::PLATE,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 2,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::PLATE,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::PLATE,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 3,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
            // --------- CLASS 4:
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 2,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 3,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::PLATE,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::PLATE,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 2,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::PLATE,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::PLATE,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 3,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
            // --------- CLASS 5:
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_5,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 2,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_5,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 3,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
            // --------- CLASS 7:
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 2,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 3,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::PLATE,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::PLATE,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 2,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::PLATE,
                'parkingBrakeTypeCode' => BrakeTestTypeCode::PLATE,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'wheelsLocked' => 3,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
                'expectedMinimumLockThreshold' => 2
            ],
        ];
    }

    /**
     * @dataProvider withSingleServiceBrake_itIsFailingOnLowEfficiencyDP
     *
     * @param $vehicleClassCode
     * @param $serviceBrakeTypeCode
     * @param $isCommercialVehicle
     * @param $dateOfFirstUse
     * @param $serviceBrake1Efficiency
     * @param $expectedCalculationFailureSeverity
     * @param $expectedMinimumThreshold
     */
    public function testCreateServiceBrakeCalculationResult_withSingleServiceBrake_itIsFailingOnLowEfficiency(
        $vehicleClassCode,
        $serviceBrakeTypeCode,
        $isCommercialVehicle,
        $dateOfFirstUse,
        $serviceBrake1Efficiency,
        $expectedCalculationFailureSeverity,
        $expectedMinimumThreshold
    )
    {
        $this->withVehicleOfClass($vehicleClassCode, $dateOfFirstUse);
        $this->withCommercialVehicle($isCommercialVehicle);
        $this->withSingleServiceBrakeDataProvided($serviceBrake1Efficiency);

        /** @var ServiceBrakeCalculationResult $calculationResult */
        $calculationResult = $this->sut->createServiceBrakeCalculationResult(
            $this->vehicle,
            $serviceBrakeTypeCode,
            $this->brakeTestResultClass3AndAbove,
            self::SERVICE_BRAKE_1
        );

        $failMsg = sprintf(
            "Test failed with %d efficiency - Minimum threshold to PASS is %d",
            $serviceBrake1Efficiency,
            $expectedMinimumThreshold
        );

        $this->assertTrue($serviceBrake1Efficiency < $expectedMinimumThreshold, $failMsg);
        $this->assertFalse($calculationResult->isPassing());
        $this->assertEquals($calculationResult->getFailureSeverity(), $expectedCalculationFailureSeverity);
    }


    public function withSingleServiceBrake_itIsFailingOnLowEfficiencyDP()
    {
        return [
            // --------- CLASS 3:
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 49,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 25,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 24,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES,
            ],
            // for old vehicles of class 3 there is lower efficiency threshold
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY, '-1 day'),
                'serviceBrake1Efficiency' => 39,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_3_PRE_1968
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY, '-1 day'),
                'serviceBrake1Efficiency' => 20,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_3_PRE_1968
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY, '-1 day'),
                'serviceBrake1Efficiency' => 19,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_3_PRE_1968
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY, '-1 day'),
                'serviceBrake1Efficiency' => 0,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_3_PRE_1968
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 49,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 25,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 24,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 0,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 49,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 25,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 24,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_3_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 0,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            // --------- CLASS 4:
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '-1 day'),
                'serviceBrake1Efficiency' => 49,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '-1 day'),
                'serviceBrake1Efficiency' => 25,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '-1 day'),
                'serviceBrake1Efficiency' => 24,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '-1 day'),
                'serviceBrake1Efficiency' => 0,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            // for class 4 with fistDateOfUse >= '2010-09-01' there are different thresholds for commercial and non-commercial vehicles
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 57,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 29,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 28,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 0,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => true,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 49,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010_COMMERCIAL
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => true,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 25,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010_COMMERCIAL
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => true,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 24,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010_COMMERCIAL
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => true,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY),
                'serviceBrake1Efficiency' => 0,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010_COMMERCIAL
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 57,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 29,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 28,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 0,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => true,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 49,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010_COMMERCIAL
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => true,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 25,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010_COMMERCIAL
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => true,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 24,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010_COMMERCIAL
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => true,
                'dateOfFirstUse' => $this->getDate(BrakeTestResultClass3AndAbove::DATE_BEFORE_CLASS_4_LOWER_EFFICIENCY, '+1 day'),
                'serviceBrake1Efficiency' => 0,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_CLASS_4_POST_2010_COMMERCIAL
            ],
            // --------- CLASS 5:
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_5,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 49,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_5,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 25,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_5,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 24,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_5,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 0,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            // --------- CLASS 7:
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 49,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 25,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 24,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'isCommercialVehicle' => false,
                'dateOfFirstUse' => $this->getDate(self::DEFAULT_DATE),
                'serviceBrake1Efficiency' => 24,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS,
                'expectedMinimumThreshold' => BrakeTestResultClass3AndAboveCalculator::EFFICIENCY_SERVICE_BRAKE_ALL_CLASSES
            ],
        ];
    }

    /**
     * Tests two service brakes for vehicle class 3 (with 2 controls) at once
     * Calculation are done om service brake separately but
     * whether is passes or fails depends also on the efficiency % of the second service brake
     *
     * @dataProvider class3VehicleWithTwoServiceBrakesDP
     *
     * @param string $vehicleClassCode
     * @param string $serviceBrakeTypeCode
     * @param int $serviceBrake1Efficiency
     * @param int $serviceBrake2Efficiency
     * @param bool $isFirstServiceBrakePassing
     * @param bool $isSecondServiceBrakePassing
     * @param string $expectedCalculationFailureSeverity1
     * @param string $expectedCalculationFailureSeverity2
     */
    public function testCreateServiceBrakeCalculationResult_class3VehicleWithTwoServiceBrakes(
        $vehicleClassCode,
        $serviceBrakeTypeCode,
        $serviceBrake1Efficiency,
        $serviceBrake2Efficiency,
        $isFirstServiceBrakePassing,
        $isSecondServiceBrakePassing,
        $expectedCalculationFailureSeverity1,
        $expectedCalculationFailureSeverity2
    )
    {
        $this->withVehicleOfClass($vehicleClassCode);
        $this->withTwoServiceBrakeDataProvided($serviceBrake1Efficiency, $serviceBrake2Efficiency);

        /** @var ServiceBrakeCalculationResult $calculationResult1 */
        $calculationResult1 = $this->sut->createServiceBrakeCalculationResult(
            $this->vehicle,
            $serviceBrakeTypeCode,
            $this->brakeTestResultClass3AndAbove,
            self::SERVICE_BRAKE_1
        );

        /** @var ServiceBrakeCalculationResult $calculationResult2 */
        $calculationResult2 = $this->sut->createServiceBrakeCalculationResult(
            $this->vehicle,
            $serviceBrakeTypeCode,
            $this->brakeTestResultClass3AndAbove,
            self::SERVICE_BRAKE_2
        );

        $this->assertEquals(
            $isFirstServiceBrakePassing,
            $calculationResult1->isPassing(),
            sprintf("First service brake should %s with efficiency %d", true === $isFirstServiceBrakePassing ? "PASS" : "FAIL", $serviceBrake1Efficiency)
        );
        $this->assertEquals(
            $calculationResult1->getFailureSeverity(),
            $expectedCalculationFailureSeverity1,
            "Failure severity of first service brake doesn't match expectation"
        );

        $this->assertEquals(
            $isSecondServiceBrakePassing,
            $calculationResult2->isPassing(),
            sprintf("Second service brake should %s with efficiency %d", true === $isSecondServiceBrakePassing ? "PASS" : "FAIL", $serviceBrake2Efficiency)
        );
        $this->assertEquals(
            $calculationResult2->getFailureSeverity(),
            $expectedCalculationFailureSeverity2,
            "Failure severity of second service brake doesn't match expectation"
        );
    }

    public function class3VehicleWithTwoServiceBrakesDP()
    {
        return [
            // @see checkEfficiencyForTwoServiceBrakes @ BrakeTestResultClass3AndAboveCalculator
            // @see checkIfEfficiencyForTwoServiceBrakesIsPassingDangerousLevelThreshold @ BrakeTestResultClass3AndAboveCalculator
            // >=30% is a Pass (per single service brake)
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 30,
                'serviceBrake2Efficiency' => 30,
                'isFirstServiceBrakePassing' => true,
                'isSecondServiceBrakePassing' => true,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::NONE,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::NONE,
            ],
            // 25% on one service brake is acceptable only if the other service brake is >=30 - otherwise it's a major defect
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 25,
                'serviceBrake2Efficiency' => 30,
                'isFirstServiceBrakePassing' => true,
                'isSecondServiceBrakePassing' => true,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::NONE,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::NONE,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 30,
                'serviceBrake2Efficiency' => 25,
                'isFirstServiceBrakePassing' => true,
                'isSecondServiceBrakePassing' => true,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::NONE,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::NONE,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 25,
                'serviceBrake2Efficiency' => 25,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::MAJOR,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::MAJOR,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 40,
                'serviceBrake2Efficiency' => 0,
                'isFirstServiceBrakePassing' => true,
                'isSecondServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::NONE,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::DANGEROUS,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 0,
                'serviceBrake2Efficiency' => 40,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => true,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::DANGEROUS,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::NONE,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 15,
                'serviceBrake2Efficiency' => 0,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::MAJOR,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::DANGEROUS,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 0,
                'serviceBrake2Efficiency' => 15,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::DANGEROUS,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::MAJOR,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 14,
                'serviceBrake2Efficiency' => 0,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::DANGEROUS,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::DANGEROUS,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 0,
                'serviceBrake2Efficiency' => 14,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::DANGEROUS,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::DANGEROUS,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 29,
                'serviceBrake2Efficiency' => 0,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::MAJOR,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::DANGEROUS,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 0,
                'serviceBrake2Efficiency' => 29,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::DANGEROUS,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::MAJOR,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 16,
                'serviceBrake2Efficiency' => 40,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => true,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::MAJOR,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::NONE,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 14,
                'serviceBrake2Efficiency' => 40,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => true,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::MAJOR,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::NONE,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 13,
                'serviceBrake2Efficiency' => 40,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => true,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::MAJOR,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::NONE,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 13,
                'serviceBrake2Efficiency' => 29,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::MAJOR,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::MAJOR,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 12,
                'serviceBrake2Efficiency' => 40,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => true,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::DANGEROUS,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::NONE,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 12,
                'serviceBrake2Efficiency' => 29,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::DANGEROUS,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::MAJOR,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 13,
                'serviceBrake2Efficiency' => 14,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::DANGEROUS,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::DANGEROUS,
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 13,
                'serviceBrake2Efficiency' => 16,
                'isFirstServiceBrakePassing' => false,
                'isSecondServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity1' => CalculationFailureSeverity::MAJOR,
                'expectedCalculationFailureSeverity2' => CalculationFailureSeverity::MAJOR,
            ],
        ];
    }

    /**
     * @dataProvider class7UnladenDP
     *
     * @param string $vehicleClassCode
     * @param string $serviceBrakeTypeCode
     * @param int $serviceBrake1Efficiency
     * @param bool $isUnladenVehicleWeight
     * @param array $effortAndLocksValues
     * @param bool $isServiceBrakePassing
     * @param string $expectedCalculationFailureSeverity
     */
    public function testCreateServiceBrakeCalculationResult_class7Unladen(
        $vehicleClassCode,
        $serviceBrakeTypeCode,
        $serviceBrake1Efficiency,
        $isUnladenVehicleWeight,
        $effortAndLocksValues,
        $isServiceBrakePassing,
        $expectedCalculationFailureSeverity
    )
    {
        $this->withVehicleOfClass($vehicleClassCode);
        $this->withUnladenVehicleWeight($isUnladenVehicleWeight);
        $this->withSingleServiceBrakeDataProvided($serviceBrake1Efficiency);
        $this->withEffortAndLockValuesProvided($effortAndLocksValues);

        /** @var ServiceBrakeCalculationResult $calculationResult */
        $calculationResult = $this->sut->createServiceBrakeCalculationResult(
            $this->vehicle,
            $serviceBrakeTypeCode,
            $this->brakeTestResultClass3AndAbove,
            self::SERVICE_BRAKE_1
        );

        $this->assertEquals(
            $isServiceBrakePassing,
            $calculationResult->isPassing(),
            sprintf("Service brake test should %s", true === $isServiceBrakePassing ? "PASS" : "FAIL")
        );

        $this->assertEquals(
            $expectedCalculationFailureSeverity,
            $calculationResult->getFailureSeverity(),
            "Failure severity of first service brake doesn't match expectation"
        );

    }

    public function class7UnladenDP()
    {
        return [
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 0,
                'isUnladenVehicleWeight' => true,
                'effortAndLocksValues' =>
                    $this->getEffortAndLocksArray(
                        100, true,
                        100, true,
                        50, 50,
                        50, 50
                    ),
                'isServiceBrakePassing' => true,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::NONE
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 0,
                'isUnladenVehicleWeight' => true,
                'effortAndLocksValues' =>
                    $this->getEffortAndLocksArray(
                        99, false,
                        99, false,
                        49, 49,
                        49, 49
                    ),
                'isServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 0,
                'isUnladenVehicleWeight' => true,
                'effortAndLocksValues' =>
                    $this->getEffortAndLocksArray(
                        99, true,
                        99, true,
                        49, 49,
                        49, 49
                    ),
                'isServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS
            ],
            [
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'serviceBrakeTypeCode' => BrakeTestTypeCode::ROLLER,
                'serviceBrake1Efficiency' => 0,
                'isUnladenVehicleWeight' => true,
                'effortAndLocksValues' =>
                    $this->getEffortAndLocksArray(
                        100, false,
                        100, true,
                        50, 50,
                        50, 50
                    ),
                'isServiceBrakePassing' => false,
                'expectedCalculationFailureSeverity' => CalculationFailureSeverity::DANGEROUS
            ],
        ];
    }

    /**
     * @param int $vehicleClassCode
     * @param \DateTime $dateOfFirstUse
     */
    private function withVehicleOfClass($vehicleClassCode, \DateTime $dateOfFirstUse = null)
    {
        $vehicleClass = new VehicleClass($vehicleClassCode);
        $this->vehicle->setFirstUsedDate($dateOfFirstUse);

        $this->vehicle
            ->getModelDetail()
            ->setVehicleClass($vehicleClass);
    }

    /**
     * @param bool $ftValue
     */
    private function withFeatureToggle($ftValue)
    {
        $this->featureToggles
            ->expects($this->any())
            ->method('isEnabled')
            ->with(FeatureToggle::EU_ROADWORTHINESS)
            ->willReturn($ftValue);
    }

    /**
     * @param int $serviceBrake1Efficiency
     */
    private function withSingleServiceBrakeDataProvided($serviceBrake1Efficiency)
    {
        // assumption - only one service brake is being tested (this excludes class 3 with 2 controls vehicles)
        $this->withServiceBrakeDataProvided($serviceBrake1Efficiency, null);
    }

    /**
     * @param int $serviceBrake1Efficiency
     * @param int $serviceBrake2Efficiency
     */
    private function withTwoServiceBrakeDataProvided($serviceBrake1Efficiency, $serviceBrake2Efficiency)
    {
        $this->withServiceBrakeDataProvided($serviceBrake1Efficiency, $serviceBrake2Efficiency);
    }

    /**
     * @param int $serviceBrake1Efficiency
     * @param int|null $serviceBrake2Efficiency
     */
    private function withServiceBrakeDataProvided($serviceBrake1Efficiency, $serviceBrake2Efficiency = null)
    {
        $serviceBrake1DataEntity = new BrakeTestResultServiceBrakeData();
        $serviceBrake2DataEntity = new BrakeTestResultServiceBrakeData();

        // for single service brake case: nullify the 2nd service brake related data
        if(null === $serviceBrake2Efficiency){
            $serviceBrake2DataEntity = null;
            $this->brakeTestResultClass3AndAbove
                ->setServiceBrake2TestType(null);
        }

        $this->brakeTestResultClass3AndAbove
            ->setServiceBrake1Data($serviceBrake1DataEntity)
            ->setServiceBrake1Efficiency($serviceBrake1Efficiency)
            ->setServiceBrake2Data($serviceBrake2DataEntity)
            ->setServiceBrake2Efficiency($serviceBrake2Efficiency)
        ;
    }

    /**
     * @param bool $isCommercialVehicle
     */
    private function withCommercialVehicle($isCommercialVehicle)
    {
        if(is_bool($isCommercialVehicle)) {
            $this->brakeTestResultClass3AndAbove->setIsCommercialVehicle($isCommercialVehicle);
        }
        else {
            $this->brakeTestResultClass3AndAbove->setIsCommercialVehicle(false);
        }
    }

    /**
     * @param string $dateString
     * @param null|string $modifyByString
     * 
     * @return \DateTime
     */
    private function getDate($dateString, $modifyByString = null)
    {
        $dateObj = new \DateTime($dateString);
        if(null !== $modifyByString){
            $dateObj = $dateObj->modify($modifyByString);
        }

        return $dateObj;
    }

    /**
     * @param $wheelsLocked
     */
    private function withNumberOfWheelsLockedOnServiceBrakeOne($wheelsLocked)
    {
        $toLock = $wheelsLocked;
        /** @var BrakeTestResultServiceBrakeData $serviceBrake1Data */
        $serviceBrake1Data = $this->brakeTestResultClass3AndAbove->getServiceBrake1Data();

        foreach($this->getAllAxles() as $axleNumber)
        {
            if($toLock <= 0 || $toLock > 6) {
                break;
            }

            foreach($this->getAllSides() as $sideOfVehicle)
            {
                if($toLock <= 0 || $toLock > 6) {
                    break;
                }
                // we need to initialise the effort value on given locked wheel per side/axle
                $setEffortMethodNameTemplate = sprintf('setEffort%sAxle%d', $sideOfVehicle, $axleNumber);
                if(method_exists($serviceBrake1Data, $setEffortMethodNameTemplate)) {
                    $serviceBrake1Data->$setEffortMethodNameTemplate(0);

                }

                // lock the wheel
                $setLockMethodNameTemplate = sprintf('setLock%sAxle%d', $sideOfVehicle, $axleNumber);
                if(method_exists($serviceBrake1Data, $setLockMethodNameTemplate)) {
                    $serviceBrake1Data->$setLockMethodNameTemplate(true);
                }
                $toLock--;
            }
        }

        $this->brakeTestResultClass3AndAbove->setServiceBrake1Data($serviceBrake1Data);
    }

    /**
     * @return array
     */
    private function getAllAxles()
    {
        return [self::AXLE_1, self::AXLE_2, self::AXLE_3];
    }

    /**
     * @return array
     */
    private function getAllSides()
    {
        return [self::NEARSIDE, self::OFFSIDE];
    }

    /**
     * @param $parkingBrakeTypeCode
     */
    private function withParkingBrakeTestType($parkingBrakeTypeCode)
    {
        $this->setParkingBrakeTestType($parkingBrakeTypeCode);
    }

    /**
     * @param string $type
     */
    private function setParkingBrakeTestType($type = BrakeTestTypeCode::DECELEROMETER)
    {
        $parkingBrakeTestType = new BrakeTestType();
        $parkingBrakeTestType->setCode($type);

        $this->brakeTestResultClass3AndAbove->setParkingBrakeTestType($parkingBrakeTestType);
    }

    /**
     * @param bool $isUnladen
     */
    private function withUnladenVehicleWeight($isUnladen = true)
    {
        $this->brakeTestResultClass3AndAbove->setWeightIsUnladen($isUnladen);
    }

    /**
     * @param array $effortAndLocksValues
     */
    private function withEffortAndLockValuesProvided(array $effortAndLocksValues)
    {
        /** @var BrakeTestResultServiceBrakeData $serviceBrake1Data */
        $serviceBrake1Data = $this->brakeTestResultClass3AndAbove->getServiceBrake1Data();

        foreach ($effortAndLocksValues as $methodToCall => $paramValue)
        {
            if (method_exists($serviceBrake1Data, $methodToCall)) {
                $serviceBrake1Data->$methodToCall($paramValue);
            }
        }

        $this->brakeTestResultClass3AndAbove->setServiceBrake1Data($serviceBrake1Data);
    }

    /**
     * Generates array to be used in withEffortAndLockValuesProvided() method
     *
     * @param $setEffortNearsideAxle1
     * @param $setLockNearsideAxle1
     * @param $setEffortOffsideAxle1
     * @param $setLockOffsideAxle1
     * @param $setEffortNearsideAxle2
     * @param $setEffortOffsideAxle2
     * @param $setEffortNearsideAxle3
     * @param $setEffortOffsideAxle3
     *
     * @return array
     */
    private function getEffortAndLocksArray(
        $setEffortNearsideAxle1,
        $setLockNearsideAxle1,
        $setEffortOffsideAxle1,
        $setLockOffsideAxle1,
        $setEffortNearsideAxle2,
        $setEffortOffsideAxle2,
        $setEffortNearsideAxle3,
        $setEffortOffsideAxle3
    )
    {
        return [
            'setEffortNearsideAxle1' => $setEffortNearsideAxle1,
            'setLockNearsideAxle1' => $setLockNearsideAxle1,
            'setEffortOffsideAxle1' => $setEffortOffsideAxle1,
            'setLockOffsideAxle1' => $setLockOffsideAxle1,
            'setEffortNearsideAxle2' => $setEffortNearsideAxle2,
            'setEffortOffsideAxle2' => $setEffortOffsideAxle2,
            'setEffortNearsideAxle3' => $setEffortNearsideAxle3,
            'setEffortOffsideAxle3' => $setEffortOffsideAxle3,
        ];
    }
}