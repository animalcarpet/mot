<?php

namespace DvsaMotApiTest\Mapper;

use DvsaCommon\Constants\FeatureToggle;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Mapper\ParkingBrakeClass3AndAboveRfrMapper;
use DvsaMotApi\Service\Calculator\BrakeTestClass3AndAboveCalculationResult;
use DvsaMotApi\Service\Calculator\CalculationFailureSeverity;
use DvsaMotApi\Service\Calculator\ParkingBrakeCalculationResult;
use DvsaMotApi\Service\Calculator\ServiceBrakeCalculationResult;
use DvsaMotApi\Service\Model\BrakeTestResultSubmissionSummary;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use DvsaCommon\Enum\BrakeTestTypeCode;

class ParkingBrakeClass3AndAboveRfrMapperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BrakeTestResultClass3AndAbove
     */
    private $brakeTestResult;

    /** @var BrakeTestResultSubmissionSummary */
    private $summary;

    /** @var FeatureToggles|MockObject */
    private $featureToggles;

    /** @var string */
    private $parkingBrakeTestTypeCode;

    /** @var BrakeTestClass3AndAboveCalculationResult */
    private $calculationResult;

    public function setup()
    {
        $this->featureToggles = Xmock::of(FeatureToggles::class);
        $this->brakeTestResult = new BrakeTestResultClass3AndAbove();
        $this->summary = new BrakeTestResultSubmissionSummary();
    }

    /**
     * @dataProvider dataProviderPostEuParkingBrakeTestTypeFailExpectedRfr
     * @param $brakeTestType
     * @param $failureSeverity
     * @param $expectedRfr
     */
    public function testPostEuRfrSAreCorrectlyMapped($brakeTestType, $failureSeverity, $expectedRfr)
    {
        $this->setEuRoadworthinessFeatureToggle(true);

        $this->parkingBrakeTestTypeCode = $brakeTestType;
        $this->brakeTestResult->setParkingBrakeEfficiencyPass(false);

        $pbCalculationResult = new ParkingBrakeCalculationResult(true, $failureSeverity);
        $sbCalculationResult1 = new ServiceBrakeCalculationResult(true, CalculationFailureSeverity::NONE);

        $this->calculationResult = new BrakeTestClass3AndAboveCalculationResult(
            $this->brakeTestResult,
            $pbCalculationResult,
            $sbCalculationResult1);

        $parkingBrakeClass3AndAboveRfrMapper = new ParkingBrakeClass3AndAboveRfrMapper($this->featureToggles);
        $parkingBrakeClass3AndAboveRfrMapper->generateParkingBrakeLowEfficiencyRfr(
            $this->brakeTestResult,
            $this->summary,
            $this->parkingBrakeTestTypeCode,
            $this->calculationResult);

        $this->assertEquals($expectedRfr, $this->summary->reasonsForRejectionList[0]['rfrId']);
    }

    /**
     * @dataProvider dataProviderPreEuParkingBrakeTestTypeFailExpectedRfr
     * @param $brakeTestType
     * @param $expectedRfr
     */
    public function testPreEuRfrSAreCorrectlyMapped($brakeTestType,$expectedRfr)
    {
        $this->setEuRoadworthinessFeatureToggle(false);

        $this->parkingBrakeTestTypeCode = $brakeTestType;
        $this->brakeTestResult->setParkingBrakeEfficiencyPass(false);

        $parkingBrakeClass3AndAboveRfrMapper = new ParkingBrakeClass3AndAboveRfrMapper($this->featureToggles);
        $parkingBrakeClass3AndAboveRfrMapper->generateParkingBrakeLowEfficiencyRfr(
            $this->brakeTestResult,
            $this->summary,
            $this->parkingBrakeTestTypeCode);

        $this->assertEquals($expectedRfr, $this->summary->reasonsForRejectionList[0]['rfrId']);
    }

    public function dataProviderPreEuParkingBrakeTestTypeFailExpectedRfr()
    {
        return [
            [
                BrakeTestTypeCode::ROLLER,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_ROLLER_LOW_EFFICIENCY
            ],
            [
                BrakeTestTypeCode::PLATE,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_PLATE_LOW_EFFICIENCY
            ],
            [
                BrakeTestTypeCode::DECELEROMETER,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_DECELEROMETER_LOW_EFFICIENCY
            ],
            [
                BrakeTestTypeCode::GRADIENT,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_GRADIENT_LOW_EFFICIENCY
            ],
        ];
    }

    public function dataProviderPostEuParkingBrakeTestTypeFailExpectedRfr()
    {
        return[
            [
                BrakeTestTypeCode::ROLLER,
                CalculationFailureSeverity::DANGEROUS,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_ROLLER_LOW_EFFICIENCY_DANGEROUS
            ],
            [
                BrakeTestTypeCode::ROLLER,
                CalculationFailureSeverity::MAJOR,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_ROLLER_LOW_EFFICIENCY_MAJOR
            ],
            [
                BrakeTestTypeCode::PLATE,
                CalculationFailureSeverity::DANGEROUS,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_PLATE_LOW_EFFICIENCY_DANGEROUS
            ],
            [
                BrakeTestTypeCode::PLATE,
                CalculationFailureSeverity::MAJOR,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_PLATE_LOW_EFFICIENCY_MAJOR
            ],
            [
                BrakeTestTypeCode::DECELEROMETER,
                CalculationFailureSeverity::DANGEROUS,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_DECELEROMETER_LOW_EFFICIENCY_DANGEROUS
            ],
            [
                BrakeTestTypeCode::DECELEROMETER,
                CalculationFailureSeverity::MAJOR,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_DECELEROMETER_LOW_EFFICIENCY_MAJOR
            ],
            [
                BrakeTestTypeCode::GRADIENT,
                CalculationFailureSeverity::MAJOR,
                ParkingBrakeClass3AndAboveRfrMapper::RFR_ID_PARKING_BRAKE_GRADIENT_LOW_EFFICIENCY_MAJOR
            ],
        ];
    }

    private function setEuRoadworthinessFeatureToggle($euRoadworthinessFeatureToggleValue) {

        $this->featureToggles->expects($this->any())
            ->method("isEnabled")
            ->with(FeatureToggle::EU_ROADWORTHINESS)
            ->willReturn($euRoadworthinessFeatureToggleValue);
    }
}