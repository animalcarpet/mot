<?php

namespace DvsaCommon\Enum;

/**
 * Enum class generated from the 'reason_for_rejection_type' table
 *
 * DO NOT EDIT! -- THIS CLASS IS GENERATED BY mot-common-web-module/generate_enums.php
 */
class ReasonForRejectionTypeName
{
    const ADVISORY = 'ADVISORY';
    const FAIL = 'FAIL';
    const PRS = 'PRS';
    const NON_SPECIFIC = 'NON SPECIFIC';
    const SYSTEM_GENERATED = 'SYSTEM GENERATED';
    const USER_ENTERED = 'USER ENTERED';

    /**
     * @return array of values for the type ReasonForRejectionTypeName
     */
    public static function getAll()
    {
        return [
            self::ADVISORY,
            self::FAIL,
            self::PRS,
            self::NON_SPECIFIC,
            self::SYSTEM_GENERATED,
            self::USER_ENTERED,
        ];
    }

    /**
     * @param mixed $key a candidate ReasonForRejectionTypeName value
     *
     * @return true if $key is in the list of known values for the type
     */
    public static function exists($key)
    {
        return in_array($key, self::getAll(), true);
    }
}
