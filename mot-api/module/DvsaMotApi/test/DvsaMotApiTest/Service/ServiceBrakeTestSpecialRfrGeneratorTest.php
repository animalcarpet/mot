<?php

namespace DvsaMotApiTest\Service;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaMotApi\Service\Calculator\BrakeTestClass3AndAboveCalculationResult;
use DvsaMotApi\Service\Calculator\CalculationFailureSeverity;
use DvsaMotApi\Service\Calculator\ServiceBrakeCalculationResult;
use DvsaMotApi\Service\Model\BrakeTestResultSubmissionSummary;
use DvsaMotApi\Service\ServiceBrakeTestSpecialRfrGenerator;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use DvsaMotApi\Service\MotTestReasonForRejectionService;

class ServiceBrakeTestSpecialRfrGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ServiceBrakeTestSpecialRfrGenerator */
    private $sut;

    /** @var BrakeTestResultClass3AndAbove */
    private $brakeTestResult;
    /** @var BrakeTestResultSubmissionSummary */
    private $brakeTestResultSubmissionSummary;
    /** @var BrakeTestClass3AndAboveCalculationResult|MockObject */
    private $brakeTestClass3AndAboveCalculationResult;

    public function setUp()
    {
        $this->sut = new ServiceBrakeTestSpecialRfrGenerator();
        $this->brakeTestResult = new BrakeTestResultClass3AndAbove();
        $this->brakeTestResultSubmissionSummary = new BrakeTestResultSubmissionSummary();
        $this->brakeTestClass3AndAboveCalculationResult = XMock::of(BrakeTestClass3AndAboveCalculationResult::class);
    }

    /**
     * @dataProvider noneOfServiceBrakesAreFailing_DP
     *
     * @param string $serviceBrakeTestTypeCode
     * @param bool $isEuRoadWorthinessEnabled
     */
    public function testGenerateRfr_itDoesNotGenerateAnyRfr_ifNoneOfServiceBrakesAreFailing(
        $serviceBrakeTestTypeCode,
        $isEuRoadWorthinessEnabled
    )
    {
        $this->withPassingServiceBrakeOne();
        $this->withPassingServiceBrakeTwo();

        $this->sut->generateRfr(
            $this->brakeTestResult,
            $this->brakeTestResultSubmissionSummary,
            $serviceBrakeTestTypeCode,
            $this->brakeTestClass3AndAboveCalculationResult,
            $isEuRoadWorthinessEnabled
        );

        $this->assertEmpty($this->brakeTestResultSubmissionSummary->reasonsForRejectionList, "No Rfrs should be generated");
    }

    public function noneOfServiceBrakesAreFailing_DP()
    {
        return [
            ['serviceBrakeTestTypeCode' => BrakeTestTypeCode::ROLLER, 'isEuRoadWorthinessEnabled' => false],
            ['serviceBrakeTestTypeCode' => BrakeTestTypeCode::PLATE, 'isEuRoadWorthinessEnabled' => false],
            ['serviceBrakeTestTypeCode' => BrakeTestTypeCode::DECELEROMETER, 'isEuRoadWorthinessEnabled' => false],

            ['serviceBrakeTestTypeCode' => BrakeTestTypeCode::ROLLER, 'isEuRoadWorthinessEnabled' => true],
            ['serviceBrakeTestTypeCode' => BrakeTestTypeCode::PLATE, 'isEuRoadWorthinessEnabled' => true],
            ['serviceBrakeTestTypeCode' => BrakeTestTypeCode::DECELEROMETER, 'isEuRoadWorthinessEnabled' => true],
        ];
    }

    /**
     * @dataProvider failingServiceBrakesWithSeverityDP
     *
     * @param bool $isEuRoadWorthinessEnabled
     * @param string $severitySB1
     * @param string $severitySB2
     * @param string $serviceBrakeTestTypeCode
     *
     * @param array $expectedRfrList
     */
    public function testGenerateRfr_itGeneratesSpecialRfrs_accordinglyToSeverityLevel(
        $isEuRoadWorthinessEnabled,
        $severitySB1,
        $severitySB2,
        $serviceBrakeTestTypeCode,
        $expectedRfrList
    )
    {
        $this->withFailingServiceBrakeOne($severitySB1);
        $this->withFailingServiceBrakeTwo($severitySB2);

        $this->sut->generateRfr(
            $this->brakeTestResult,
            $this->brakeTestResultSubmissionSummary,
            $serviceBrakeTestTypeCode,
            $this->brakeTestClass3AndAboveCalculationResult,
            $isEuRoadWorthinessEnabled
        );

        $generatedRfrs = $this->brakeTestResultSubmissionSummary->reasonsForRejectionList;

        $this->assertNotEmpty($generatedRfrs);
        $this->assertEquals($expectedRfrList, $generatedRfrs);
    }

    public function failingServiceBrakesWithSeverityDP()
    {
        return [
            // for feature toggle OFF - severity is not taken into account
            [
                'isEuRoadWorthinessEnabled' => false,
                'severitySB1' => CalculationFailureSeverity::NONE,
                'severitySB2' => CalculationFailureSeverity::NONE,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::ROLLER,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY),
                ],
            ],
            [
                'isEuRoadWorthinessEnabled' => false,
                'severitySB1' => CalculationFailureSeverity::NONE,
                'severitySB2' => CalculationFailureSeverity::NONE,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::PLATE,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY),
                ],
            ],
            [
                'isEuRoadWorthinessEnabled' => false,
                'severitySB1' => CalculationFailureSeverity::NONE,
                'severitySB2' => CalculationFailureSeverity::NONE,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::DECELEROMETER,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY),
                ],
            ],
            // for feature toggle ON - severity is onne of the deciding factors for choosing RFR
            // for cases with 2 service brake (vehicle class 3 with 2 controls) - we generate separate RFR for each failing service brake
            // Dangerous Failures:
            [
                'isEuRoadWorthinessEnabled' => true,
                'severitySB1' => CalculationFailureSeverity::DANGEROUS,
                'severitySB2' => CalculationFailureSeverity::DANGEROUS,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::ROLLER,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY),
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY),
                ],
            ],
            [
                'isEuRoadWorthinessEnabled' => true,
                'severitySB1' => CalculationFailureSeverity::DANGEROUS,
                'severitySB2' => CalculationFailureSeverity::DANGEROUS,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::PLATE,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY),
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY),
                ],
            ],
            [
                'isEuRoadWorthinessEnabled' => true,
                'severitySB1' => CalculationFailureSeverity::DANGEROUS,
                'severitySB2' => CalculationFailureSeverity::DANGEROUS,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::DECELEROMETER,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY),
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY),
                ],
            ],
            // Major Failures:
            [
                'isEuRoadWorthinessEnabled' => true,
                'severitySB1' => CalculationFailureSeverity::MAJOR,
                'severitySB2' => CalculationFailureSeverity::MAJOR,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::ROLLER,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY),
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY),
                ],
            ],
            [
                'isEuRoadWorthinessEnabled' => true,
                'severitySB1' => CalculationFailureSeverity::MAJOR,
                'severitySB2' => CalculationFailureSeverity::MAJOR,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::PLATE,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY),
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY),
                ],
            ],
            [
                'isEuRoadWorthinessEnabled' => true,
                'severitySB1' => CalculationFailureSeverity::MAJOR,
                'severitySB2' => CalculationFailureSeverity::MAJOR,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::DECELEROMETER,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY),
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY),
                ],
            ],
            // Mixed severity failures:
            [
                'isEuRoadWorthinessEnabled' => true,
                'severitySB1' => CalculationFailureSeverity::DANGEROUS,
                'severitySB2' => CalculationFailureSeverity::MAJOR,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::ROLLER,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY),
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY),
                ],
            ],
            [
                'isEuRoadWorthinessEnabled' => true,
                'severitySB1' => CalculationFailureSeverity::MAJOR,
                'severitySB2' => CalculationFailureSeverity::DANGEROUS,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::ROLLER,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY),
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY),
                ],
            ],
            [
                'isEuRoadWorthinessEnabled' => true,
                'severitySB1' => CalculationFailureSeverity::DANGEROUS,
                'severitySB2' => CalculationFailureSeverity::MAJOR,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::PLATE,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY),
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY),
                ],
            ],
            [
                'isEuRoadWorthinessEnabled' => true,
                'severitySB1' => CalculationFailureSeverity::MAJOR,
                'severitySB2' => CalculationFailureSeverity::DANGEROUS,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::PLATE,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY),
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY),
                ],
            ],
            [
                'isEuRoadWorthinessEnabled' => true,
                'severitySB1' => CalculationFailureSeverity::DANGEROUS,
                'severitySB2' => CalculationFailureSeverity::MAJOR,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::DECELEROMETER,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY),
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY),
                ],
            ],
            [
                'isEuRoadWorthinessEnabled' => true,
                'severitySB1' => CalculationFailureSeverity::MAJOR,
                'severitySB2' => CalculationFailureSeverity::DANGEROUS,
                'serviceBrakeTestTypeCode' => BrakeTestTypeCode::DECELEROMETER,
                'expectedRfrList' => [
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY),
                    $this->getRfr(ServiceBrakeTestSpecialRfrGenerator::RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY),
                ],
            ],
        ];
    }

    private function withPassingServiceBrakeOne()
    {
        $this->setUpServiceBrakeValues(1, true, CalculationFailureSeverity::NONE);
    }

    private function withPassingServiceBrakeTwo()
    {
        $this->setUpServiceBrakeValues(2, true, CalculationFailureSeverity::NONE);
    }

    private function withFailingServiceBrakeOne($severity)
    {
        $this->setUpServiceBrakeValues(1, false, $severity);
    }

    private function withFailingServiceBrakeTwo($severity)
    {
        $this->setUpServiceBrakeValues(2, false, $severity);
    }

    /**
     * @param int $serviceBrakeIndex
     * @param bool $isPassing
     * @param string $severity
     */
    private function setUpServiceBrakeValues($serviceBrakeIndex, $isPassing, $severity)
    {
        if($serviceBrakeIndex == 1) {
            $this->brakeTestResult->setServiceBrake1EfficiencyPass($isPassing);
            $this->brakeTestClass3AndAboveCalculationResult
                ->expects($this->any())
                ->method('getServiceBrakeCalculationResult1')
                ->willReturn(
                    $this->generateServiceBrakeCalculationResultStub($isPassing, $severity)
                );
        }
        else {
            $this->brakeTestResult->setServiceBrake2EfficiencyPass($isPassing);
            $this->brakeTestClass3AndAboveCalculationResult
                ->expects($this->any())
                ->method('getServiceBrakeCalculationResult2')
                ->willReturn(
                    $this->generateServiceBrakeCalculationResultStub($isPassing, $severity)
                );
        }
    }

    /**
     * @param bool $isPassing
     * @param string $severity
     *
     * @return ServiceBrakeCalculationResult
     */
    private function generateServiceBrakeCalculationResultStub($isPassing, $severity)
    {
        return new ServiceBrakeCalculationResult(
            $isPassing,
            $severity
        );
    }

    /**
     * @param $rfrId
     * @return array
     */
    public function getRfr($rfrId)
    {
        $summary = new BrakeTestResultSubmissionSummary();
        $summary->addReasonForRejection($rfrId);

        foreach($summary->reasonsForRejectionList as $rfr)
        {
            if ($rfr[MotTestReasonForRejectionService::RFR_ID_FIELD] == $rfrId)
            {
                return $rfr;
            }
        }
        return null;
    }
}