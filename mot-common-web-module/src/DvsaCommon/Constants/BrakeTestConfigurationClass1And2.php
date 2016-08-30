<?php

namespace DvsaCommon\Constants;


use DvsaCommon\Enum\BrakeTestTypeCode;

class BrakeTestConfigurationClass1And2
{
    public static $locksApplicable = [
        BrakeTestTypeCode::ROLLER,
        BrakeTestTypeCode::PLATE,
        BrakeTestTypeCode::FLOOR,
    ];

    /**
     * @param string $brakeTestType
     * @return bool
     */
    public static function isLockApplicableToTestType($brakeTestType){
        return in_array($brakeTestType, static::$locksApplicable);
    }
}