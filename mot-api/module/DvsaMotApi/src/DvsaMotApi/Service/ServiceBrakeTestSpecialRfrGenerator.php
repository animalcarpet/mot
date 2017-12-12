<?php
declare(strict_types=1);

namespace DvsaMotApi\Service;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaMotApi\Service\Calculator\BrakeTestClass3AndAboveCalculationResult;
use DvsaMotApi\Service\Calculator\CalculationFailureSeverity;
use DvsaMotApi\Service\Model\BrakeTestResultSubmissionSummary;

class ServiceBrakeTestSpecialRfrGenerator
{
    // pre EU:
    const RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY = '8357';
    // post EU:
    const RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY = '30009';
    const RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY = '30006';

    // pre EU:
    const RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY = '8371';
    // post EU:
    const RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY = '30008';
    const RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY = '30005';

    // pre EU:
    const RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY = '8365';
    // post EU:
    const RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY = '30010';
    const RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY = '30007';

    /**
     * @param BrakeTestResultClass3AndAbove $brakeTestResult
     * @param BrakeTestResultSubmissionSummary $summary
     * @param string $serviceBrakeTestTypeCode
     * @param BrakeTestClass3AndAboveCalculationResult $calculationResult
     * @param bool $isEuRoadWorthinessEnabled
     */
    public function generateRfr(
        BrakeTestResultClass3AndAbove $brakeTestResult,
        BrakeTestResultSubmissionSummary $summary,
        string $serviceBrakeTestTypeCode,
        BrakeTestClass3AndAboveCalculationResult $calculationResult,
        bool $isEuRoadWorthinessEnabled
    )
    {
        if(true === $isEuRoadWorthinessEnabled){
            $this->generateServiceBrakeLowEfficiencyRfrEuRoadworthiness($brakeTestResult, $summary, $serviceBrakeTestTypeCode, $calculationResult);
        }
        else {
            $this->generateServiceBrakeLowEfficiencyRfrPreEu($brakeTestResult, $summary, $serviceBrakeTestTypeCode);
        }
    }

    /**
     * @param BrakeTestResultClass3AndAbove $brakeTestResult
     * @param BrakeTestResultSubmissionSummary $summary
     * @param string $serviceBrakeTestTypeCode
     * @param BrakeTestClass3AndAboveCalculationResult $calculationResult
     * @internal param bool $isEuRoadWorthinessEnabled
     */
    private function generateServiceBrakeLowEfficiencyRfrEuRoadworthiness(
        BrakeTestResultClass3AndAbove $brakeTestResult,
        BrakeTestResultSubmissionSummary $summary,
        $serviceBrakeTestTypeCode,
        BrakeTestClass3AndAboveCalculationResult $calculationResult
    )
    {
        // we need to generate Special Processing Rfr for each service brake separately and take into account the severity of a failure
        if ($brakeTestResult->getServiceBrake1EfficiencyPass() === false) {
            $serviceBrake1FailureSeverity = $calculationResult->getServiceBrakeCalculationResult1()->getFailureSeverity();
            $rfrId = $this->getRfrServiceBrakeLowEfficiency(true, $serviceBrakeTestTypeCode, $serviceBrake1FailureSeverity);
            $summary->addReasonForRejection($rfrId);
        }

        if ($brakeTestResult->getServiceBrake2EfficiencyPass() === false) {
            $serviceBrake2FailureSeverity = $calculationResult->getServiceBrakeCalculationResult2()->getFailureSeverity();
            $rfrId = $this->getRfrServiceBrakeLowEfficiency(true, $serviceBrakeTestTypeCode, $serviceBrake2FailureSeverity);
            $summary->addReasonForRejection($rfrId);
        }
    }


    /**
     * @param BrakeTestResultClass3AndAbove $brakeTestResult
     * @param BrakeTestResultSubmissionSummary $summary
     * @param string $serviceBrakeTestTypeCode
     */
    private function generateServiceBrakeLowEfficiencyRfrPreEu(
        BrakeTestResultClass3AndAbove $brakeTestResult,
        BrakeTestResultSubmissionSummary $summary,
        $serviceBrakeTestTypeCode
    )
    {
        if ($brakeTestResult->getServiceBrake1EfficiencyPass() === false
            || $brakeTestResult->getServiceBrake2EfficiencyPass() === false
        ) {
            $rfrId = $this->getRfrServiceBrakeLowEfficiency(false, $serviceBrakeTestTypeCode);
            $summary->addReasonForRejection($rfrId);
        }
    }

    /**
     * Gets RFR ID for low service brake efficiency depending on which brake test type was conducted
     *
     * @param bool $isEuRoadWorthinessEnabled
     * @param string $brakeTestTypeCode
     * @param $failureSeverity
     * @return null|string
     */
    private function getRfrServiceBrakeLowEfficiency(bool $isEuRoadWorthinessEnabled, string $brakeTestTypeCode, $failureSeverity = null)
    {
        if(true === $isEuRoadWorthinessEnabled) {
            return $this->getRfrServiceBrakeLowEfficiencyPostEuRoadworthiness($brakeTestTypeCode, $failureSeverity);
        }
        else {
            return $this->getRfrServiceBrakeLowEfficiencyPreEu($brakeTestTypeCode);
        }
    }

    /**
     * @param string $brakeTestTypeCode
     * @param $failureSeverity
     *
     * @return null|string
     */
    private function getRfrServiceBrakeLowEfficiencyPostEuRoadworthiness($brakeTestTypeCode, $failureSeverity)
    {
        if($failureSeverity === CalculationFailureSeverity::DANGEROUS) {
            return $this->getDangarousRfrServiceBrakeLowEfficiency($brakeTestTypeCode);
        }

        if($failureSeverity === CalculationFailureSeverity::MAJOR) {
            return $this->getMajorRfrServiceBrakeLowEfficiency($brakeTestTypeCode);
        }

        return null;
    }

    /**
     * @param $brakeTestTypeCode
     * @return null|string
     */
    private function getDangarousRfrServiceBrakeLowEfficiency($brakeTestTypeCode)
    {
        switch ($brakeTestTypeCode) {
            case BrakeTestTypeCode::ROLLER:
                return self::RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY;
            case BrakeTestTypeCode::PLATE:
                return self::RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY;
            case BrakeTestTypeCode::DECELEROMETER:
                return self::RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY_DANGEROUS_EU_DEFICIENCY;
        }

        return null;
    }

    /**
     * @param $brakeTestTypeCode
     * @return null|string
     */
    private function getMajorRfrServiceBrakeLowEfficiency($brakeTestTypeCode)
    {
        switch ($brakeTestTypeCode) {
            case BrakeTestTypeCode::ROLLER:
                return self::RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY;
            case BrakeTestTypeCode::PLATE:
                return self::RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY;
            case BrakeTestTypeCode::DECELEROMETER:
                return self::RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY_MAJOR_EU_DEFICIENCY;
        }

        return null;
    }


    /**
     * @param string $brakeTestTypeCode
     * @return null|string
     */
    private function getRfrServiceBrakeLowEfficiencyPreEu($brakeTestTypeCode)
    {
        switch ($brakeTestTypeCode) {
            case BrakeTestTypeCode::ROLLER:
                return self::RFR_ID_SERVICE_BRAKE_ROLLER_LOW_EFFICIENCY;
            case BrakeTestTypeCode::PLATE:
                return self::RFR_ID_SERVICE_BRAKE_PLATE_LOW_EFFICIENCY;
            case BrakeTestTypeCode::DECELEROMETER:
                return self::RFR_ID_SERVICE_BRAKE_DECELEROMETER_LOW_EFFICIENCY;
        }

        return null;
    }

}