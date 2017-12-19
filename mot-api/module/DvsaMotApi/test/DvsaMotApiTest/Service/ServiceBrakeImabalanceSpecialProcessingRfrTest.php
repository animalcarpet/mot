<?php
/**
 * Created by PhpStorm.
 * User: markpatt
 * Date: 11/12/2017
 * Time: 14:04
 */

namespace DvsaMotApiTest\Service;

use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\BrakeTestResultServiceBrakeData;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntitiesTest\Entity\BrakeTestTypeFactory;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Mapper\ServiceBrakeImbalanceSpecialProcessingRfrMapper;
use DvsaMotApi\Service\Calculator\BrakeImbalanceResult;
use DvsaMotApi\Service\Calculator\BrakeTestClass3AndAboveCalculationResult;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculator;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculatorBase;
use DvsaMotApi\Service\Calculator\CalculationFailureSeverity;
use DvsaMotApi\Service\Model\BrakeTestResultSubmissionSummary;
use PHPUnit\Framework\TestCase;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\InvalidArgumentException;

class ServiceBrakeImabalanceSpecialProcessingRfrTest extends TestCase
{
    /** @var  ServiceBrakeImbalanceSpecialProcessingRfrMapper */
    private $serviceBrakeMapper;

    /** @var  Vehicle */
    private $vehicle;

    /** @var BrakeTestResultClass3AndAbove */
    private $brakeTestResultClass3AndAbove;

    /** @var  BrakeTestResultClass3AndAboveCalculator */
    private $brakeTest3AndAboveCalculator;

    /** @var BrakeTestClass3AndAboveCalculationResult|MockObject */
    private $brakeTestClass3AndAboveCalculationResult;

    /** @var  FeatureToggles | MockObject */
    private $featureToggles;

    /** @var  BrakeTestResultServiceBrakeData */
    private $serviceBrakeData;

    public function setUp()
    {
        $this->setUpVehicleDefaults();
        $this->serviceBrakeData = new BrakeTestResultServiceBrakeData();
        $this->brakeTestResultClass3AndAbove = new BrakeTestResultClass3AndAbove();
        $this->featureToggles = XMock::of(FeatureToggles::class);
    }

    public function setUpVehicleDefaults()
    {
        $this->vehicle = new Vehicle();
        $modelDetail = new ModelDetail();

        $this->vehicle->setModelDetail($modelDetail);
    }

    /**
     * @dataProvider testServiceBrakeImbalanceDualLine_FailsWithSpecialProcessingIDs_IfBrakeImbalanceIsHigherThanThresholdAndWheelIsNotLockedDP
     *
     * @param $axle1EffortNearside
     * @param $axle1EffortOffside
     * @param $axle1LockNearside
     * @param $axle1LockOffside
     * @param $axle2EffortNearside
     * @param $axle2EffortOffside
     * @param $axle2LockNearside
     * @param $axle2LockOffside
     * @param $vehicleClassCode
     * @param $vehicleWeight
     * @param $expectedToPass
     * @param $expectedFailureSeverity
     */
    public function testServiceBrakeImbalanceDualLine_FailsWithSpecialProcessingIDs_IfBrakeImbalanceIsHigherThanThresholdAndWheelIsNotLocked(
        $brakeTestType, $axle1EffortNearside, $axle1EffortOffside, $axle1LockNearside, $axle1LockOffside,
        $axle2EffortNearside, $axle2EffortOffside, $axle2LockNearside, $axle2LockOffside,
        $vehicleClassCode, $vehicleWeight, $expectedToPass, $expectedFailureSeverity, $axleToCompare, $expectedRFR
    )
    {
        $this->enableEUFeatureToggle(true);

        $this->brakeTest3AndAboveCalculator = new BrakeTestResultClass3AndAboveCalculator($this->featureToggles);
        $this->serviceBrakeMapper = new ServiceBrakeImbalanceSpecialProcessingRfrMapper($this->featureToggles);

        $this->setVehicleClassAndWeight($vehicleClassCode, $vehicleWeight);
         $data = $this->initialiseMockBrakeTestResult(
            $axle1EffortNearside, $axle1EffortOffside, $axle1LockNearside, $axle1LockOffside,
            $axle2EffortNearside, $axle2EffortOffside, $axle2LockNearside, $axle2LockOffside);

        $mockBrakeTestResult = $this->brakeTestResultClass3AndAbove->setServiceBrake1Data($data);
        $mockBrakeTestResult->setServiceBrake1TestType($brakeTestType);
        $mockBrakeTestResult->setParkingBrakeTestType(BrakeTestTypeFactory::type(BrakeTestTypeCode::DECELEROMETER));
        $mockBrakeTestResult->setVehicleWeight($this->vehicle->getWeight());

        $calculationResult = $this->brakeTest3AndAboveCalculator->calculateBrakeTestResult(
            $mockBrakeTestResult, $this->vehicle
        );

        $isPassingAxle = $calculationResult->getBrakeImbalanceResult()->isImbalanceOverallPass();
        $actualSeverity = $calculationResult->getBrakeImbalanceResult()->getAxleImbalanceSeverity($axleToCompare);
        $actualRFR = $this->serviceBrakeMapper->generateServiceBrakeImbalanceRfr(
            $this->brakeTestResultClass3AndAbove->getServiceBrake1TestType()->getCode(),
            $actualSeverity
        );

        $this->assertEquals($isPassingAxle, $expectedToPass);
        $this->assertEquals($expectedFailureSeverity, $actualSeverity);
        $this->assertEquals($actualRFR, $expectedRFR);
    }

    /**
     * @return array
     */
    public function testServiceBrakeImbalanceDualLine_FailsWithSpecialProcessingIDs_IfBrakeImbalanceIsHigherThanThresholdAndWheelIsNotLockedDP()
    {
        return [
            // class 3, 4, 5, 7 - Plate
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::PLATE),
                'axle1EffortNearside' => 69,
                'axle1EffortOffside' => 100,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_PLATE_IMBALANCE_EU_MAJOR
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::PLATE),
                'axle1EffortNearside' => 69,
                'axle1EffortOffside' => 100,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_PLATE_IMBALANCE_EU_MAJOR
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::PLATE),
                'axle1EffortNearside' => 69,
                'axle1EffortOffside' => 100,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_PLATE_IMBALANCE_EU_MAJOR
            ],
            // class 3, 4, 5, 7 - Roller
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::ROLLER),
                'axle1EffortNearside' => 69,
                'axle1EffortOffside' => 100,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_ROLLER_IMBALANCE_EU_MAJOR
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::ROLLER),
                'axle1EffortNearside' => 69,
                'axle1EffortOffside' => 100,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_ROLLER_IMBALANCE_EU_MAJOR
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::ROLLER),
                'axle1EffortNearside' => 69,
                'axle1EffortOffside' => 100,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_5,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_ROLLER_IMBALANCE_EU_MAJOR
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::ROLLER),
                'axle1EffortNearside' => 69,
                'axle1EffortOffside' => 100,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::MAJOR,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_ROLLER_IMBALANCE_EU_MAJOR
            ]
        ];
    }


    /**
     * @dataProvider testServiceBrakeImbalanceDualLine_Passes_IfBrakeImbalanceIsHigherThanThresholdAndWheelIsLockedDP
     *
     * @param $axle1EffortNearside
     * @param $axle1EffortOffside
     * @param $axle1LockNearside
     * @param $axle1LockOffside
     * @param $axle2EffortNearside
     * @param $axle2EffortOffside
     * @param $axle2LockNearside
     * @param $axle2LockOffside
     * @param $vehicleClassCode
     * @param $vehicleWeight
     * @param $expectedToPass
     * @param $expectedFailureSeverity
     */
    public function testServiceBrakeImbalanceDualLine_Passes_IfBrakeImbalanceIsHigherThanThresholdAndWheelIsLocked(
        $brakeTestType, $axle1EffortNearside, $axle1EffortOffside, $axle1LockNearside, $axle1LockOffside,
        $axle2EffortNearside, $axle2EffortOffside, $axle2LockNearside, $axle2LockOffside,
        $vehicleClassCode, $vehicleWeight, $expectedToPass, $expectedFailureSeverity, $axleToCompare
    )
    {
        $this->enableEUFeatureToggle(true);

        $this->brakeTest3AndAboveCalculator = new BrakeTestResultClass3AndAboveCalculator($this->featureToggles);
        $this->serviceBrakeMapper = new ServiceBrakeImbalanceSpecialProcessingRfrMapper($this->featureToggles);

        $this->setVehicleClassAndWeight($vehicleClassCode, $vehicleWeight);
        $data = $this->initialiseMockBrakeTestResult(
            $axle1EffortNearside, $axle1EffortOffside, $axle1LockNearside, $axle1LockOffside,
            $axle2EffortNearside, $axle2EffortOffside, $axle2LockNearside, $axle2LockOffside);

        $mockBrakeTestResult = $this->brakeTestResultClass3AndAbove->setServiceBrake1Data($data);
        $mockBrakeTestResult->setServiceBrake1TestType($brakeTestType);
        $mockBrakeTestResult->setParkingBrakeTestType(BrakeTestTypeFactory::type(BrakeTestTypeCode::DECELEROMETER));
        $mockBrakeTestResult->setVehicleWeight($this->vehicle->getWeight());

        $calculationResult = $this->brakeTest3AndAboveCalculator->calculateBrakeTestResult(
            $mockBrakeTestResult, $this->vehicle
        );

        $isPassingAxle = $calculationResult->getBrakeImbalanceResult()->isImbalanceOverallPass();
        $actualSeverity = $calculationResult->getBrakeImbalanceResult()->getAxleImbalanceSeverity($axleToCompare);

        $this->assertEquals($isPassingAxle, $expectedToPass);
        $this->assertEquals($expectedFailureSeverity, $actualSeverity);
    }

    /**
     * @return array
     */
    public function testServiceBrakeImbalanceDualLine_Passes_IfBrakeImbalanceIsHigherThanThresholdAndWheelIsLockedDP()
    {
        return [
            // class 3, 4, 5, 7 - Plate
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::PLATE),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => true,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'vehicleWeight' => 1000,
                'expectedToPass' => true,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::PLATE),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => true,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'vehicleWeight' => 1000,
                'expectedToPass' => true,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::PLATE),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => true,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'vehicleWeight' => 1000,
                'expectedToPass' => true,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1
            ],
            // class 3, 4, 5, 7 - Roller
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::ROLLER),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => true,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'vehicleWeight' => 1000,
                'expectedToPass' => true,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::ROLLER),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => true,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'vehicleWeight' => 1000,
                'expectedToPass' => true,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::ROLLER),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => true,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_5,
                'vehicleWeight' => 1000,
                'expectedToPass' => true,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::ROLLER),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => true,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'vehicleWeight' => 1000,
                'expectedToPass' => true,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1
            ]
        ];
    }

    /**
     * @dataProvider testServiceBrakeImbalanceDualLine_FailsWithPreEuIDs_IfBrakeImbalanceIsHigherThanThresholdAndFeatureToggleIsOffDP
     *
     * @param $axle1EffortNearside
     * @param $axle1EffortOffside
     * @param $axle1LockNearside
     * @param $axle1LockOffside
     * @param $axle2EffortNearside
     * @param $axle2EffortOffside
     * @param $axle2LockNearside
     * @param $axle2LockOffside
     * @param $vehicleClassCode
     * @param $vehicleWeight
     * @param $expectedToPass
     * @param $expectedFailureSeverity
     */
    public function testServiceBrakeImbalanceDualLine_FailsWithPreEuIDs_IfBrakeImbalanceIsHigherThanThresholdAndFeatureToggleIsOff(
        $brakeTestType, $axle1EffortNearside, $axle1EffortOffside, $axle1LockNearside, $axle1LockOffside,
        $axle2EffortNearside, $axle2EffortOffside, $axle2LockNearside, $axle2LockOffside,
        $vehicleClassCode, $vehicleWeight, $expectedToPass, $expectedFailureSeverity, $axleToCompare, $expectedRFR
    )
    {
        $this->enableEUFeatureToggle(false);

        $this->brakeTest3AndAboveCalculator = new BrakeTestResultClass3AndAboveCalculator($this->featureToggles);
        $this->serviceBrakeMapper = new ServiceBrakeImbalanceSpecialProcessingRfrMapper($this->featureToggles);

        $this->setVehicleClassAndWeight($vehicleClassCode, $vehicleWeight);
        $data = $this->initialiseMockBrakeTestResult(
            $axle1EffortNearside, $axle1EffortOffside, $axle1LockNearside, $axle1LockOffside,
            $axle2EffortNearside, $axle2EffortOffside, $axle2LockNearside, $axle2LockOffside);

        $mockBrakeTestResult = $this->brakeTestResultClass3AndAbove->setServiceBrake1Data($data);
        $mockBrakeTestResult->setServiceBrake1TestType($brakeTestType);
        $mockBrakeTestResult->setParkingBrakeTestType(BrakeTestTypeFactory::type(BrakeTestTypeCode::DECELEROMETER));
        $mockBrakeTestResult->setVehicleWeight($this->vehicle->getWeight());

        $calculationResult = $this->brakeTest3AndAboveCalculator->calculateBrakeTestResult(
            $mockBrakeTestResult, $this->vehicle
        );

        $isPassingAxle = $calculationResult->getBrakeImbalanceResult()->isImbalanceOverallPass();
        $actualSeverity = $calculationResult->getBrakeImbalanceResult()->getAxleImbalanceSeverity($axleToCompare);
        $actualRFR = $this->serviceBrakeMapper->generateServiceBrakeImbalanceRfr(
            $this->brakeTestResultClass3AndAbove->getServiceBrake1TestType()->getCode(),
            $actualSeverity
        );

        $this->assertEquals($isPassingAxle, $expectedToPass);
        $this->assertEquals($expectedFailureSeverity, $actualSeverity);
        $this->assertEquals($actualRFR, $expectedRFR);
    }

    /**
     * @return array
     */
    public function testServiceBrakeImbalanceDualLine_FailsWithPreEuIDs_IfBrakeImbalanceIsHigherThanThresholdAndFeatureToggleIsOffDP()
    {
        return [
            // class 3, 4, 7 - Plate
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::PLATE),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_PLATE_IMBALANCE
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::PLATE),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_PLATE_IMBALANCE
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::PLATE),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_PLATE_IMBALANCE
            ],
            // class 3, 4, 5, 7 - Roller
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::ROLLER),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_ROLLER_IMBALANCE
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::ROLLER),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_ROLLER_IMBALANCE
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::ROLLER),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_5,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_ROLLER_IMBALANCE
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::ROLLER),
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'vehicleWeight' => 1000,
                'expectedToPass' => false,
                'expectedFailureSeverity' => CalculationFailureSeverity::NONE,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1,
                'expectedRFR' => ServiceBrakeImbalanceSpecialProcessingRfrMapper::RFR_ID_SERVICE_BRAKE_ROLLER_IMBALANCE
            ]
        ];
    }

    /**
     * @throws InvalidArgumentException
     * @dataProvider testServiceBrakeImbalanceDualLine_ThrowsInvalidArgumentException_IfInvalidBrakeTestTypeSubmittedDP
     *
     * @param $axle1EffortNearside
     * @param $axle1EffortOffside
     * @param $axle1LockNearside
     * @param $axle1LockOffside
     * @param $axle2EffortNearside
     * @param $axle2EffortOffside
     * @param $axle2LockNearside
     * @param $axle2LockOffside
     * @param $vehicleClassCode
     * @param $vehicleWeight
     * @param $expectedToPass
     * @param $expectedFailureSeverity
     */
    public function testServiceBrakeImbalanceDualLine_ThrowsInvalidArgumentException_IfInvalidBrakeTestTypeSubmitted(
        $brakeTestType, $brakeTestTypeCode, $axle1EffortNearside, $axle1EffortOffside, $axle1LockNearside, $axle1LockOffside,
        $axle2EffortNearside, $axle2EffortOffside, $axle2LockNearside, $axle2LockOffside,
        $vehicleClassCode, $vehicleWeight, $axleToCompare
    )
    {
        $this->enableEUFeatureToggle(true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid brake test type code $brakeTestTypeCode");

        $this->brakeTest3AndAboveCalculator = new BrakeTestResultClass3AndAboveCalculator($this->featureToggles);
        $this->serviceBrakeMapper = new ServiceBrakeImbalanceSpecialProcessingRfrMapper($this->featureToggles);

        $this->setVehicleClassAndWeight($vehicleClassCode, $vehicleWeight);
        $data = $this->initialiseMockBrakeTestResult(
            $axle1EffortNearside, $axle1EffortOffside, $axle1LockNearside, $axle1LockOffside,
            $axle2EffortNearside, $axle2EffortOffside, $axle2LockNearside, $axle2LockOffside);

        $mockBrakeTestResult = $this->brakeTestResultClass3AndAbove->setServiceBrake1Data($data);
        $mockBrakeTestResult->setServiceBrake1TestType($brakeTestType);
        $mockBrakeTestResult->setParkingBrakeTestType(BrakeTestTypeFactory::type(BrakeTestTypeCode::DECELEROMETER));
        $mockBrakeTestResult->setVehicleWeight($this->vehicle->getWeight());

        $calculationResult = $this->brakeTest3AndAboveCalculator->calculateBrakeTestResult(
            $mockBrakeTestResult, $this->vehicle
        );

        $actualSeverity = $calculationResult->getBrakeImbalanceResult()->getAxleImbalanceSeverity($axleToCompare);
        $this->serviceBrakeMapper->generateServiceBrakeImbalanceRfr(
            $this->brakeTestResultClass3AndAbove->getServiceBrake1TestType()->getCode(),
            $actualSeverity
        );
    }

    /**
     * @return array
     */
    public function testServiceBrakeImbalanceDualLine_ThrowsInvalidArgumentException_IfInvalidBrakeTestTypeSubmittedDP()
    {
        return [
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::DECELEROMETER),
                'brakeTestTypeCode' => BrakeTestTypeCode::DECELEROMETER,
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_3,
                'vehicleWeight' => 1000,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::FLOOR),
                'brakeTestTypeCode' => BrakeTestTypeCode::FLOOR,
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_4,
                'vehicleWeight' => 1000,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::DECELEROMETER),
                'brakeTestTypeCode' => BrakeTestTypeCode::DECELEROMETER,
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_5,
                'vehicleWeight' => 1000,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1
            ],
            [
                'brakeTestType' => BrakeTestTypeFactory::type(BrakeTestTypeCode::FLOOR),
                'brakeTestTypeCode' => BrakeTestTypeCode::FLOOR,
                'axle1EffortNearside' => 100,
                'axle1EffortOffside' => 69,
                'axle1LockNearside' => false,
                'axle1LockOffside' => false,
                'axle2EffortNearside' => 100,
                'axle2EffortOffside' => 100,
                'axle2LockNearside' => false,
                'axle2LockOffside' => false,
                'vehicleClassCode' => VehicleClassCode::CLASS_7,
                'vehicleWeight' => 1000,
                'axleToCompare' => BrakeImbalanceResult::AXLE_1
            ],
        ];
    }

    /**
     * @param $axle1EffortNearside
     * @param $axle1EffortOffside
     * @param $axle1LockNearside
     * @param $axle1LockOffside
     * @param $axle2EffortNearside
     * @param $axle2EffortOffside
     * @param $axle2LockNearside
     * @param $axle2LockOffside
     * @return BrakeTestResultServiceBrakeData
     */
    private function initialiseMockBrakeTestResult(
        $axle1EffortNearside, $axle1EffortOffside, $axle1LockNearside, $axle1LockOffside,
        $axle2EffortNearside, $axle2EffortOffside, $axle2LockNearside, $axle2LockOffside
    )
    {
        $this->serviceBrakeData->setEffortNearsideAxle1($axle1EffortNearside);
        $this->serviceBrakeData->setEffortOffsideAxle1($axle1EffortOffside);
        $this->serviceBrakeData->setLockNearsideAxle1($axle1LockNearside);
        $this->serviceBrakeData->setLockOffsideAxle1($axle1LockOffside);

        $this->serviceBrakeData->setEffortNearsideAxle2($axle2EffortNearside);
        $this->serviceBrakeData->setEffortOffsideAxle2($axle2EffortOffside);
        $this->serviceBrakeData->setLockNearsideAxle2($axle2LockNearside);
        $this->serviceBrakeData->setLockOffsideAxle2($axle2LockOffside);

        return $this->serviceBrakeData;
    }

    /**
     * @param $vehicleClassCode
     * @param $vehicleWeight
     */
    private function setVehicleClassAndWeight($vehicleClassCode, $vehicleWeight)
    {
        $vehicleClass = new VehicleClass($vehicleClassCode);
        $this->vehicle->getModelDetail()->setVehicleClass($vehicleClass);

        $this->vehicle->setWeight($vehicleWeight);
    }

    /**
     * @param $toggleValue
     */
    private function enableEUFeatureToggle($toggleValue)
    {
        $this->featureToggles->expects($this->any())
            ->method('isEnabled')
            ->with(FeatureToggle::EU_ROADWORTHINESS)
            ->willReturn($toggleValue);
    }
}
