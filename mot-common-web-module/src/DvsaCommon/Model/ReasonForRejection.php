<?php

namespace DvsaCommon\Model;

use DvsaCommon\Enum\ReasonForRejectionTypeName;

class ReasonForRejection
{
    protected static $testQualityInformationSkippedRfrTypes = [
        ReasonForRejectionTypeName::ADVISORY,
        ReasonForRejectionTypeName::NON_SPECIFIC,
        ReasonForRejectionTypeName::USER_ENTERED,
    ];

    /**
     * @return array
     */
    public static function getTestQualityInformationSkippedRfrTypes()
    {
        return static::$testQualityInformationSkippedRfrTypes;
    }
}