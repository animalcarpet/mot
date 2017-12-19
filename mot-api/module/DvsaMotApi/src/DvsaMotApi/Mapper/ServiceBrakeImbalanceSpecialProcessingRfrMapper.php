<?php
/**
 * Created by PhpStorm.
 * User: markpatt
 * Date: 11/12/2017
 * Time: 14:18
 */

namespace DvsaMotApi\Mapper;


use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Service\Calculator\CalculationFailureSeverity;

class ServiceBrakeImbalanceSpecialProcessingRfrMapper
{

    const RFR_ID_SERVICE_BRAKE_ROLLER_IMBALANCE = '8343';
    const RFR_ID_SERVICE_BRAKE_PLATE_IMBALANCE = '8370';
    const RFR_ID_SERVICE_BRAKE_PLATE_IMBALANCE_EU_MAJOR = '30001';
    const RFR_ID_SERVICE_BRAKE_ROLLER_IMBALANCE_EU_MAJOR = '30002';

    /** @var FeatureToggles */
    private $featureToggles;

    public function __construct($featureToggles)
    {
     $this->featureToggles = $featureToggles;
    }

    public function generateServiceBrakeImbalanceRfr($serviceBrakeTestType, $imbalanceSeverity)
    {
        if ($this->featureToggles->isEnabled(FeatureToggle::EU_ROADWORTHINESS)) {
            return $this->getRfrServiceBrakeImbalancedPostEU($serviceBrakeTestType, $imbalanceSeverity);
        } else {
            return $this->getRfrServiceBrakeImbalancedPreEU($serviceBrakeTestType);
        }
    }

    private function getRfrServiceBrakeImbalancedPostEU($serviceBrakeTestType, $imbalanceSeverity)
    {
        if ($imbalanceSeverity === CalculationFailureSeverity::MAJOR) {
            switch ($serviceBrakeTestType) {
                case BrakeTestTypeCode::ROLLER:
                    return self::RFR_ID_SERVICE_BRAKE_ROLLER_IMBALANCE_EU_MAJOR;
                case BrakeTestTypeCode::PLATE:
                    return self::RFR_ID_SERVICE_BRAKE_PLATE_IMBALANCE_EU_MAJOR;
            }
        }

        return $this->getRfrServiceBrakeImbalancedPreEu($serviceBrakeTestType);
    }

    private function getRfrServiceBrakeImbalancedPreEu($serviceBrakeTestType)
    {
        switch ($serviceBrakeTestType) {
            case BrakeTestTypeCode::ROLLER:
                return self::RFR_ID_SERVICE_BRAKE_ROLLER_IMBALANCE;
            case BrakeTestTypeCode::PLATE:
                return self::RFR_ID_SERVICE_BRAKE_PLATE_IMBALANCE;
        }

        throw new \InvalidArgumentException('Invalid brake test type code ' . $serviceBrakeTestType);
    }
}