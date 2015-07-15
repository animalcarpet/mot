<?php

namespace DvsaCommon\Enum;

/**
 * Enum class generated from the 'brake_test_type' table
 *
 * DO NOT EDIT! -- THIS CLASS IS GENERATED BY mot-common-web-module/generate_enums.php
 * @codeCoverageIgnore
 */
class BrakeTestTypeCode
{
    const DECELEROMETER = 'DECEL';
    const FLOOR = 'FLOOR';
    const GRADIENT = 'GRADT';
    const PLATE = 'PLATE';
    const ROLLER = 'ROLLR';

    /**
     * @return array of values for the type BrakeTestTypeCode
     */
    public static function getAll()
    {
        return [
            self::DECELEROMETER,
            self::FLOOR,
            self::GRADIENT,
            self::PLATE,
            self::ROLLER,
        ];
    }

    /**
     * @param mixed $key a candidate BrakeTestTypeCode value
     *
     * @return true if $key is in the list of known values for the type
     */
    public static function exists($key)
    {
        return in_array($key, self::getAll(), true);
    }
}
