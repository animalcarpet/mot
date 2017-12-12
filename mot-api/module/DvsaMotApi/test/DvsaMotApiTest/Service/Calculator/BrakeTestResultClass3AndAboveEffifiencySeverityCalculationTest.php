<?php

namespace DvsaMotApiTest\Service\Calculator;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_TestCase;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculator;
use DvsaFeature\FeatureToggles;
use DvsaCommon\Constants\FeatureToggle;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Service\Calculator\CalculationFailureSeverity;

/**
 * Unit tests for the isPassingParkingBrakeEfficiency() function in BrakeTestResultClass3AndAboveCalculator class
 */
class BrakeTestResultClass3AndAboveEffifiencySeverityCalculationTest extends PHPUnit_Framework_TestCase
{
    /** @var FeatureToggles|MockObject */
    private $featureToggles;

    public function setup()
    {
        $this->featureToggles = Xmock::of(FeatureToggles::class);
    }

    public function testWhenNumberOfLockedGreaterThanMinimum_ThenPassesWithNoSeverity()
    {
        $this->setEuRoadworthinessFeatureToggle(true);

        $brakeTestResult = new BrakeTestResultClass3AndAbove();
        $brakeTestResult->setParkingBrakeLockSingle(true);
        $brakeTestResult->setParkingBrakeEffortSingle(0);

        $calculator = new BrakeTestResultClass3AndAboveCalculator($this->featureToggles);
        $parkingBrakeCalculationResult = $calculator->createParkingBrakeCalculationResult($brakeTestResult);

        $this->assertEquals(CalculationFailureSeverity::NONE, $parkingBrakeCalculationResult->getFailureSeverity());
        $this->assertTrue($parkingBrakeCalculationResult->isPassing());
    }

    /**
     * @dataProvider dataProviderForDualLineParkingBrakes
     * @param $efficiency
     * @param $lockOffside
     * @param $locknearside
     * @param $expectedToPass
     * @param $expectedSeverity
     */
    public function testVaryingEfficiencyAndLockValuesForDualLine_WillProduceTheCorrectPassAndSeverityResult(
        $efficiency,
        $lockOffside,
        $locknearside,
        $expectedToPass,
        $expectedSeverity
    )
    {
        $this->setEuRoadworthinessFeatureToggle(true);

        $brakeTestResult = new BrakeTestResultClass3AndAbove();
        $brakeTestResult->setParkingBrakeLockOffside($lockOffside);
        $brakeTestResult->setParkingBrakeEffortOffside(1);
        $brakeTestResult->setParkingBrakeLockNearside($locknearside);
        $brakeTestResult->setParkingBrakeEffortNearside(1);
        $brakeTestResult->setParkingBrakeEfficiency($efficiency);
        $brakeTestResult->setServiceBrakeIsSingleLine(false);

        $calculator = new BrakeTestResultClass3AndAboveCalculator($this->featureToggles);
        $parkingBrakeCalculationResult = $calculator->createParkingBrakeCalculationResult($brakeTestResult);

        $this->assertEquals($expectedSeverity, $parkingBrakeCalculationResult->getFailureSeverity());
        $this->assertEquals($expectedToPass, $parkingBrakeCalculationResult->isPassing());
    }

    /**
     * @dataProvider dataProviderForSingleLineParkingBrakes
     * @param $efficiency
     * @param $lockOffside
     * @param $locknearside
     * @param $expectedToPass
     * @param $expectedSeverity
     */
    public function testVaryingEfficiencyAndLockValuesForSingleLine_WillProduceTheCorrectPassAndSeverityResult(
        $efficiency,
        $lockOffside,
        $locknearside,
        $expectedToPass,
        $expectedSeverity
    )
    {
        $this->setEuRoadworthinessFeatureToggle(true);

        $brakeTestResult = new BrakeTestResultClass3AndAbove();
        $brakeTestResult->setParkingBrakeLockOffside($lockOffside);
        $brakeTestResult->setParkingBrakeEffortOffside(1);
        $brakeTestResult->setParkingBrakeLockNearside($locknearside);
        $brakeTestResult->setParkingBrakeEffortNearside(1);
        $brakeTestResult->setParkingBrakeEfficiency($efficiency);
        $brakeTestResult->setServiceBrakeIsSingleLine(true);

        $calculator = new BrakeTestResultClass3AndAboveCalculator($this->featureToggles);
        $parkingBrakeCalculationResult = $calculator->createParkingBrakeCalculationResult($brakeTestResult);

        $this->assertEquals($expectedSeverity, $parkingBrakeCalculationResult->getFailureSeverity());
        $this->assertEquals($expectedToPass, $parkingBrakeCalculationResult->isPassing());
    }


    public function dataProviderForDualLineParkingBrakes()
    {
        // efficiency %  -  lockOffside  -  locknearside  -  passes  -  severity
        return [
            // no locking, varying efficiency values testing the boundaries
            [17, false, false, true, CalculationFailureSeverity::NONE],
            [16, false, false, true, CalculationFailureSeverity::NONE],
            [15, false, false, false, CalculationFailureSeverity::MAJOR],
            [9, false, false, false, CalculationFailureSeverity::MAJOR],
            [8, false, false, false, CalculationFailureSeverity::MAJOR],
            [7, false, false, false, CalculationFailureSeverity::DANGEROUS],

            // lock only 1 wheel to test the 50% locked still checks efficiency values
            [16, true, false, true, CalculationFailureSeverity::NONE],
            [8, true, false, false, CalculationFailureSeverity::MAJOR],
            [7, true, false, false, CalculationFailureSeverity::DANGEROUS],

            // lock both wheels to test that >50% locked rule
            [16, true, true, true, CalculationFailureSeverity::NONE],
            [8, true, true, true, CalculationFailureSeverity::NONE],
            [7, true, true, true, CalculationFailureSeverity::NONE],
        ];
    }

    public function dataProviderForSingleLineParkingBrakes()
    {
        // efficiency %  -  lockOffside  -  locknearside  -  passes  -  severity
        // efficiency %  -  lockOffside  -  locknearside  -  passes  -  severity
        return [
            // no locking, varying efficiency values testing the boundaries
            [26, false, false, true, CalculationFailureSeverity::NONE],
            [25, false, false, true, CalculationFailureSeverity::NONE],
            [24, false, false, false, CalculationFailureSeverity::MAJOR],
            [13, false, false, false, CalculationFailureSeverity::MAJOR],
            [12, false, false, false, CalculationFailureSeverity::MAJOR],
            [11, false, false, false, CalculationFailureSeverity::DANGEROUS],

            // lock only 1 wheel to test the 50% locked still checks efficiency values
            [25, true, false, true, CalculationFailureSeverity::NONE],
            [12, true, false, false, CalculationFailureSeverity::MAJOR],
            [11, true, false, false, CalculationFailureSeverity::DANGEROUS],

            // lock both wheels to test that >50% locked rule
            [25, true, true, true, CalculationFailureSeverity::NONE],
            [12, true, true, true, CalculationFailureSeverity::NONE],
            [11, true, true, true, CalculationFailureSeverity::NONE],
        ];
    }
    
    private function setEuRoadworthinessFeatureToggle($euRoadworthinessFeatureToggleValue) {

        $this->featureToggles->expects($this->any())
            ->method("isEnabled")
            ->with(FeatureToggle::EU_ROADWORTHINESS)
            ->willReturn($euRoadworthinessFeatureToggleValue);
    }
}