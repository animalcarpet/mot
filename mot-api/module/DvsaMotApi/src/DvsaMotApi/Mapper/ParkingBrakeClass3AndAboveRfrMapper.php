<?php
/**
 * Created by PhpStorm.
 * User: markpatt
 * Date: 04/12/2017
 * Time: 16:26
 */

namespace DvsaMotApi\Mapper;


use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Service\Calculator\BrakeImbalanceResult;
use DvsaMotApi\Service\Calculator\BrakeTestClass3AndAboveCalculationResult;
use DvsaMotApi\Service\Calculator\CalculationFailureSeverity;
use DvsaMotApi\Service\Model\BrakeTestResultSubmissionSummary;

class ParkingBrakeClass3AndAboveRfrMapper
{
    const RFR_ID_PARKING_BRAKE_ROLLER_LOW_EFFICIENCY = '8358';
    const RFR_ID_PARKING_BRAKE_ROLLER_LOW_EFFICIENCY_MAJOR = '30012';
    const RFR_ID_PARKING_BRAKE_ROLLER_LOW_EFFICIENCY_DANGEROUS = '30016';

    const RFR_ID_PARKING_BRAKE_PLATE_LOW_EFFICIENCY = '8372';
    const RFR_ID_PARKING_BRAKE_PLATE_LOW_EFFICIENCY_MAJOR = '30011';
    const RFR_ID_PARKING_BRAKE_PLATE_LOW_EFFICIENCY_DANGEROUS = '30015';

    const RFR_ID_PARKING_BRAKE_DECELEROMETER_LOW_EFFICIENCY = '8366';
    const RFR_ID_PARKING_BRAKE_DECELEROMETER_LOW_EFFICIENCY_MAJOR = '30013';
    const RFR_ID_PARKING_BRAKE_DECELEROMETER_LOW_EFFICIENCY_DANGEROUS = '30017';

    const RFR_ID_PARKING_BRAKE_GRADIENT_LOW_EFFICIENCY = '4300';
    const RFR_ID_PARKING_BRAKE_GRADIENT_LOW_EFFICIENCY_MAJOR = '30014';

    const RFR_ID_PARKING_BRAKE_ROLLER_IMBALANCE = '8343';
    const RFR_ID_PARKING_BRAKE_PLATE_IMBALANCE = '8370';
    const RFR_ID_PARKING_BRAKE_PLATE_IMBALANCE_MAJOR = '30018';
    const RFR_ID_PARKING_BRAKE_ROLLER_IMBALANCE_MAJOR = '30019';


    /** @var FeatureToggles */
    private $featureToggles;

    public function __construct(FeatureToggles $featureToggles)
    {
        $this->featureToggles = $featureToggles;
    }

    /**
     * @param BrakeTestResultClass3AndAbove $brakeTestResult
     * @param BrakeTestResultSubmissionSummary $summary
     * @param $parkingBrakeTestTypeCode
     * @param BrakeTestClass3AndAboveCalculationResult|null $calculationResult
     */
    public function generateParkingBrakeLowEfficiencyRfr(
        BrakeTestResultClass3AndAbove $brakeTestResult,
        BrakeTestResultSubmissionSummary $summary,
        $parkingBrakeTestTypeCode,
        BrakeTestClass3AndAboveCalculationResult $calculationResult = null)
    {
        if ($this->featureToggles->isEnabled(FeatureToggle::EU_ROADWORTHINESS)) {
            $this->generateParkingBrakeLowEfficiencyRfrEURoadworthiness($brakeTestResult, $summary, $parkingBrakeTestTypeCode, $calculationResult);
        } else {
            $this->generateParkingBrakeLowEfficiencyRfrPreEu($brakeTestResult, $summary, $parkingBrakeTestTypeCode);
        }
    }

    /**
     * @param $parkingBrakeTestType
     * @return string
     */
    public function generateParkingBrakeImbalanceRfr($parkingBrakeTestType)
    {
        if ($this->featureToggles->isEnabled(FeatureToggle::EU_ROADWORTHINESS))
        {
            return $this->getRfrParkingBrakeImbalancePostEU($parkingBrakeTestType);
        } else {
            return $this->getRfrParkingBrakeImbalancePreEU($parkingBrakeTestType);
        }
    }

    /**
     * @param BrakeTestResultClass3AndAbove $brakeTestResult
     * @param BrakeTestResultSubmissionSummary $summary
     * @param $parkingBrakeTestTypeCode
     * @param BrakeTestClass3AndAboveCalculationResult|null $calculationResult
     */
    private function generateParkingBrakeLowEfficiencyRfrEURoadworthiness(
        BrakeTestResultClass3AndAbove $brakeTestResult,
        BrakeTestResultSubmissionSummary $summary,
        $parkingBrakeTestTypeCode,
        BrakeTestClass3AndAboveCalculationResult $calculationResult = null)
    {
        if ($brakeTestResult->getParkingBrakeEfficiencyPass() === false) {
            $parkingBrakeSeverity = $calculationResult->getParkingBrakeCalculationResult()->getFailureSeverity();
            $summary->addReasonForRejection($this->getRfrParkingBrakeLowEfficiency($parkingBrakeTestTypeCode, $parkingBrakeSeverity));
        }
    }

    private function getRfrParkingBrakeLowEfficiency($brakeTestTypeCode, $failureSeverity = null)
    {
        if ($this->featureToggles->isEnabled(FeatureToggle::EU_ROADWORTHINESS)) {
            return $this->getRfrParkingBrakeLowEfficiencyPostEuRoadworthiness($brakeTestTypeCode, $failureSeverity);
        } else {
            return $this->getRfrParkingBrakeLowEfficiencyPreEu($brakeTestTypeCode);
        }
    }

    /**
     * @param $brakeTestTypeCode
     * @param $failureSeverity
     * @return string
     */
    private function getRfrParkingBrakeLowEfficiencyPostEuRoadworthiness($brakeTestTypeCode, $failureSeverity)
    {
        if($failureSeverity === CalculationFailureSeverity::DANGEROUS) {
            return $this->getDangerousRfrParkingBrakeLowEfficiency($brakeTestTypeCode);
        }

        if ($failureSeverity === CalculationFailureSeverity::MAJOR) {
            return $this->getMajorRfrParkingBrakeLowEfficiency($brakeTestTypeCode);
        }

        throw new \InvalidArgumentException('Unknown failure severity ' . $failureSeverity);
    }

    /**
     * @param $brakeTestTypeCode
     * @return string
     */
    private function getDangerousRfrParkingBrakeLowEfficiency($brakeTestTypeCode)
    {
        switch ($brakeTestTypeCode) {
            case BrakeTestTypeCode::ROLLER:
                return self::RFR_ID_PARKING_BRAKE_ROLLER_LOW_EFFICIENCY_DANGEROUS;
            case BrakeTestTypeCode::PLATE:
                return self::RFR_ID_PARKING_BRAKE_PLATE_LOW_EFFICIENCY_DANGEROUS;
            case BrakeTestTypeCode::DECELEROMETER:
                return self::RFR_ID_PARKING_BRAKE_DECELEROMETER_LOW_EFFICIENCY_DANGEROUS;
        }

        throw new \InvalidArgumentException('Unknown brake test type code ' . $brakeTestTypeCode);
    }

    /**
     * @param $brakeTestTypeCode
     * @return string
     */
    private function getMajorRfrParkingBrakeLowEfficiency($brakeTestTypeCode)
    {
        switch ($brakeTestTypeCode) {
            case BrakeTestTypeCode::ROLLER:
                return self::RFR_ID_PARKING_BRAKE_ROLLER_LOW_EFFICIENCY_MAJOR;
            case BrakeTestTypeCode::PLATE:
                return self::RFR_ID_PARKING_BRAKE_PLATE_LOW_EFFICIENCY_MAJOR;
            case BrakeTestTypeCode::DECELEROMETER:
                return self::RFR_ID_PARKING_BRAKE_DECELEROMETER_LOW_EFFICIENCY_MAJOR;
            case BrakeTestTypeCode::GRADIENT:
                return self::RFR_ID_PARKING_BRAKE_GRADIENT_LOW_EFFICIENCY_MAJOR;
        }
        throw new \InvalidArgumentException('Unknown brake test type code ' . $brakeTestTypeCode);
    }

    /**
     * @param $parkingBrakeTestTypeCode
     * @return string
     */
    private function getRfrParkingBrakeLowEfficiencyPreEu($parkingBrakeTestTypeCode)
    {
        switch ($parkingBrakeTestTypeCode) {
            case BrakeTestTypeCode::ROLLER:
                return self::RFR_ID_PARKING_BRAKE_ROLLER_LOW_EFFICIENCY;
            case BrakeTestTypeCode::PLATE:
                return self::RFR_ID_PARKING_BRAKE_PLATE_LOW_EFFICIENCY;
            case BrakeTestTypeCode::DECELEROMETER:
                return self::RFR_ID_PARKING_BRAKE_DECELEROMETER_LOW_EFFICIENCY;
            case BrakeTestTypeCode::GRADIENT:
                return self::RFR_ID_PARKING_BRAKE_GRADIENT_LOW_EFFICIENCY;
        }

        throw new \InvalidArgumentException('Unknown brake test type code ' . $parkingBrakeTestTypeCode);
    }

    /**
     * @param BrakeTestResultClass3AndAbove $brakeTestResult
     * @param BrakeTestResultSubmissionSummary $summary
     * @param $parkingBrakeTestTypeCode
     */
    private function generateParkingBrakeLowEfficiencyRfrPreEu(
        BrakeTestResultClass3AndAbove $brakeTestResult,
        BrakeTestResultSubmissionSummary $summary,
        $parkingBrakeTestTypeCode)
    {
        if ($brakeTestResult->getParkingBrakeEfficiencyPass() === false) {
            $summary->addReasonForRejection($this->getRfrParkingBrakeLowEfficiency($parkingBrakeTestTypeCode));
        }
    }

    /**
     * @param $parkingBrakeTestType
     * @return string
     */
    public function getRfrParkingBrakeImbalancePostEU($parkingBrakeTestType)
    {
            switch ($parkingBrakeTestType) {
                case  BrakeTestTypeCode::PLATE:
                    return self::RFR_ID_PARKING_BRAKE_PLATE_IMBALANCE_MAJOR;
                case BrakeTestTypeCode::ROLLER:
                    return self::RFR_ID_PARKING_BRAKE_ROLLER_IMBALANCE_MAJOR;
            }

        throw new \InvalidArgumentException('Unknown brake test type code ' . $parkingBrakeTestType);
    }

    /**
     * @param $parkingBrakeTestType
     * @return string
     */
    public function getRfrParkingBrakeImbalancePreEU($parkingBrakeTestType)
    {
        switch ($parkingBrakeTestType) {
            case BrakeTestTypeCode::PLATE:
                return self::RFR_ID_PARKING_BRAKE_PLATE_IMBALANCE;
            case BrakeTestTypeCode::ROLLER:
                return self::RFR_ID_PARKING_BRAKE_ROLLER_IMBALANCE;
        }

        throw new \InvalidArgumentException('Unknown brake test type code ' . $parkingBrakeTestType);
    }
}