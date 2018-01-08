<?php
/**
 * Created by PhpStorm.
 * User: markpatt
 * Date: 04/01/2018
 * Time: 11:01
 */

namespace DvsaMotApiTest\Service;

use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Mapper\ParkingBrakeClass3AndAboveRfrMapper;
use DvsaMotApi\Service\Calculator\BrakeTestClass3AndAboveCalculationResult;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculator;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ParkingBrakeImbalanceSpecialProcessingRfrTest extends TestCase
{
    /** @var BrakeTestResultClass3AndAbove */
    private $brakeTestResult;

    /** @var FeatureToggles|MockObject */
    private $featureToggles;

    /** @var string */
    private $parkingBrakeTestTypeCode;

    /** @var BrakeTestResultClass3AndAboveCalculator */
    private $brakeTestClass3AndAboveCalculator;

    public function setup()
    {
        $this->featureToggles = Xmock::of(FeatureToggles::class);
        $this->brakeTestResult = new BrakeTestResultClass3AndAbove();
    }

    /**
     * @dataProvider dataProviderPostEuParkingBrakeTestTypeFailExpectedRfr
     * @param $brakeTestType
     * @param $expectedRfr
     */
    public function testPostEuRfrSAreCorrectlyMapped($brakeTestType, $expectedRfr)
    {
        $this->enableFeatureToggle(true);

        $this->parkingBrakeTestTypeCode = $brakeTestType;
        $this->brakeTestResult->setParkingBrakeImbalancePass(false);

        $parkingBrakeClass3AndAboveRfrMapper = new ParkingBrakeClass3AndAboveRfrMapper($this->featureToggles);
        $actualRfr = $parkingBrakeClass3AndAboveRfrMapper->generateParkingBrakeImbalanceRfr($brakeTestType);

        $this->assertEquals($expectedRfr, $actualRfr);
    }

    public function dataProviderPostEuParkingBrakeTestTypeFailExpectedRfr()
    {
        return[
            [
                BrakeTestTypeCode::PLATE,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_PLATE_IMBALANCE_MAJOR
            ],
            [
                BrakeTestTypeCode::ROLLER,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_ROLLER_IMBALANCE_MAJOR
            ],
        ];
    }

    /**
     * @dataProvider dataProviderPreEuParkingBrakeTestTypeFailExpectedRfr
     * @param $brakeTestType
     * @param $expectedRfr
     */
    public function testPreEuRfrSAreCorrectlyMapped($brakeTestType, $expectedRfr)
    {
        $this->enableFeatureToggle(false);

        $this->parkingBrakeTestTypeCode = $brakeTestType;
        $this->brakeTestResult->setParkingBrakeImbalancePass(false);

        $parkingBrakeClass3AndAboveRfrMapper = new ParkingBrakeClass3AndAboveRfrMapper($this->featureToggles);
        $actualRfr = $parkingBrakeClass3AndAboveRfrMapper->generateParkingBrakeImbalanceRfr($brakeTestType);

        $this->assertEquals($expectedRfr, $actualRfr);
    }

    public function dataProviderPreEuParkingBrakeTestTypeFailExpectedRfr()
    {
        return[
            [
                BrakeTestTypeCode::PLATE,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_PLATE_IMBALANCE
            ],
            [
                BrakeTestTypeCode::ROLLER,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_ROLLER_IMBALANCE
            ],
        ];
    }

    /**
     * @dataProvider  dataProviderTestPostEu40KiloLogicCalculation
     * @param $axleWithinLimits
     * @param $maxEffortGreaterThan40k
     * @param $effortNearside
     * @param $effortOffside
     * @param $nearsideLock
     * @param $offsideLock
     * @param $expectedPassResult
     */
    public function testPostEu40KiloLogicCalculationPrimaryAxle(
        $axleWithinLimits, $maxEffortGreaterThan40k, $effortNearside,
        $effortOffside, $nearsideLock, $offsideLock, $expectedResult
    )
    {
        $this->enableFeatureToggle(true);

        $this->brakeTestClass3AndAboveCalculator = new BrakeTestResultClass3AndAboveCalculator($this->featureToggles);

        $this->brakeTestResult->setParkingBrakeEffortNearside($effortNearside);
        $this->brakeTestResult->setParkingBrakeEffortOffside($effortOffside);
        $this->brakeTestResult->setParkingBrakeLockNearside($nearsideLock);
        $this->brakeTestResult->setParkingBrakeLockOffside($offsideLock);

        $brakeTestClass3AndAboveCalculator = new \ReflectionClass(get_class($this->brakeTestClass3AndAboveCalculator));
        $calculateImbalanceMethod = $brakeTestClass3AndAboveCalculator->getMethod('calculatePostEuSingleLineParkingBrakeImbalancePass');
        $calculateImbalanceMethod->setAccessible(true);

        $actualResult = $calculateImbalanceMethod->invokeArgs($this->brakeTestClass3AndAboveCalculator, [$axleWithinLimits, $this->brakeTestResult, $maxEffortGreaterThan40k]);

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @dataProvider  dataProviderTestPostEu40KiloLogicCalculation
     * @param $axleWithinLimits
     * @param $maxEffortGreaterThan40k
     * @param $effortNearside
     * @param $effortOffside
     * @param $nearsideLock
     * @param $offsideLock
     * @param $expectedPassResult
     */
    public function testPostEu40KiloLogicCalculationSecondaryAxle(
        $axleWithinLimits, $maxEffortGreaterThan40k, $effortNearside,
        $effortOffside, $nearsideLock, $offsideLock, $expectedResult
    )
    {
        $this->enableFeatureToggle(true);

        $this->brakeTestClass3AndAboveCalculator = new BrakeTestResultClass3AndAboveCalculator($this->featureToggles);

        $this->brakeTestResult->setParkingBrakeEffortSecondaryNearside($effortNearside);
        $this->brakeTestResult->setParkingBrakeEffortSecondaryOffside($effortOffside);
        $this->brakeTestResult->setParkingBrakeLockSecondaryNearside($nearsideLock);
        $this->brakeTestResult->setParkingBrakeLockSecondaryOffside($offsideLock);

        $brakeTestClass3AndAboveCalculator = new \ReflectionClass(get_class($this->brakeTestClass3AndAboveCalculator));
        $calculateImbalanceMethod = $brakeTestClass3AndAboveCalculator->getMethod('calculatePostEuSingleLineSecondaryParkingBrakeImbalancePass');
        $calculateImbalanceMethod->setAccessible(true);

        $actualResult = $calculateImbalanceMethod->invokeArgs($this->brakeTestClass3AndAboveCalculator, [$axleWithinLimits, $this->brakeTestResult, $maxEffortGreaterThan40k]);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function dataProviderTestPostEu40KiloLogicCalculation()
    {
        return[
            [
                'axleWithinLimits' => false,
                'maxEffortGreaterThan40k' => true,
                'effortNearside' => 300,
                'effortOffside' => 200,
                'nearsideLock' => false,
                'offsideLock' => false,
                'expectedResult' => true,
            ],
            [
                'axleWithinLimits' => false,
                'maxEffortGreaterThan40k' => true,
                'effortNearside' => 300,
                'effortOffside' => 200,
                'nearsideLock' => false,
                'offsideLock' => true,
                'expectedResult' => false,
            ],
            [
                'axleWithinLimits' => false,
                'maxEffortGreaterThan40k' => true,
                'effortNearside' => 200,
                'effortOffside' => 300,
                'nearsideLock' => false,
                'offsideLock' => false,
                'expectedResult' => true,
            ],
            [
                'axleWithinLimits' => false,
                'maxEffortGreaterThan40k' => true,
                'effortNearside' => 200,
                'effortOffside' => 300,
                'nearsideLock' => true,
                'offsideLock' => false,
                'expectedResult' => false,
            ],
            [
                'axleWithinLimits' => true,
                'maxEffortGreaterThan40k' => false,
                'effortNearside' => 300,
                'effortOffside' => 200,
                'nearsideLock' => false,
                'offsideLock' => false,
                'expectedResult' => false
            ],
            [
                'axleWithinLimits' => true,
                'maxEffortGreaterThan40k' => false,
                'effortNearside' => 300,
                'effortOffside' => 200,
                'nearsideLock' => false,
                'offsideLock' => true,
                'expectedResult' => false,
            ],
            [
                'axleWithinLimits' => true,
                'maxEffortGreaterThan40k' => false,
                'effortNearside' => 200,
                'effortOffside' => 300,
                'nearsideLock' => true,
                'offsideLock' => false,
                'expectedResult' => false,
            ],
            [
                'axleWithinLimits' => true,
                'maxEffortGreaterThan40k' => false,
                'effortNearside' => 200,
                'effortOffside' => 300,
                'nearsideLock' => true,
                'offsideLock' => false,
                'expectedResult' => false,
            ],
        ];
    }

    private function enableFeatureToggle($toggleValue)
    {
        $this->featureToggles->expects($this->any())
            ->method("isEnabled")
            ->with(FeatureToggle::EU_ROADWORTHINESS)
            ->willReturn($toggleValue);
    }
}