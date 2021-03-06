<?php

namespace DvsaCommon\Enum;

/**
 * Enum class generated from the 'transition_status' table
 *
 * DO NOT EDIT! -- THIS CLASS IS GENERATED BY mot-common-web-module/generate_enums.php
 * @codeCoverageIgnore
 */
class TransitionStatusCode
{
    const UNKNOWN = 'UNKN';
    const NOT_STARTED = 'NS';
    const SUBMITTED = 'SUB';
    const ONE_TIME_PASSWORD_ASSIGNED = 'OTPA';
    const RESTRICTED = 'REST';
    const FULL_FUNCTIONALITY = 'FULL';
    const NOT_TO_BE_TRANSITIONED = 'NOT';

    /**
     * @return array of values for the type TransitionStatusCode
     */
    public static function getAll()
    {
        return [
            self::UNKNOWN,
            self::NOT_STARTED,
            self::SUBMITTED,
            self::ONE_TIME_PASSWORD_ASSIGNED,
            self::RESTRICTED,
            self::FULL_FUNCTIONALITY,
            self::NOT_TO_BE_TRANSITIONED,
        ];
    }

    /**
     * @param mixed $key a candidate TransitionStatusCode value
     *
     * @return true if $key is in the list of known values for the type
     */
    public static function exists($key)
    {
        return in_array($key, self::getAll(), true);
    }
}
