<?php

namespace DvsaCommon\Enum;

/**
 * Enum class generated from the 'vehicle_class' table
 *
 * DO NOT EDIT! -- THIS CLASS IS GENERATED BY mot-common-web-module/generate_enums.php
 */
class VehicleClassCode
{
    const CLASS_1 = '1';
    const CLASS_2 = '2';
    const CLASS_3 = '3';
    const CLASS_4 = '4';
    const CLASS_5 = '5';
    const CLASS_7 = '7';

    /**
     * @return array of values for the type VehicleClassCode
     */
    public static function getAll()
    {
        return [
            self::CLASS_1,
            self::CLASS_2,
            self::CLASS_3,
            self::CLASS_4,
            self::CLASS_5,
            self::CLASS_7,
        ];
    }

    /**
     * @param mixed $key a candidate VehicleClassCode value
     *
     * @return true if $key is in the list of known values for the type
     */
    public static function exists($key)
    {
        return in_array($key, self::getAll(), true);
    }
}
