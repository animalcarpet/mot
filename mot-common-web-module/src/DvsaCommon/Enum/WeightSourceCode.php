<?php

namespace DvsaCommon\Enum;

/**
 * Enum class generated from the 'weight_source_lookup' table
 *
 * DO NOT EDIT! -- THIS CLASS IS GENERATED BY mot-common-web-module/generate_enums.php
 * @codeCoverageIgnore
 */
class WeightSourceCode
{
    const UNKNOWN = 'UNKN';
    const VSI = 'VSI';
    const PRESENTED = 'KERB';
    const DGW = 'DGW';
    const DGW_MAM = 'DGWM';
    const NOT_APPLICABLE = 'NA';
    const CALCULATED = 'CALC';
    const MISW = 'MISW';
    const MOTORCYCLE = 'M';
    const UNLADEN = 'U';

    /**
     * @return array of values for the type WeightSourceCode
     */
    public static function getAll()
    {
        return [
            self::UNKNOWN,
            self::VSI,
            self::PRESENTED,
            self::DGW,
            self::DGW_MAM,
            self::NOT_APPLICABLE,
            self::CALCULATED,
            self::MISW,
            self::MOTORCYCLE,
            self::UNLADEN,
        ];
    }

    /**
     * @param mixed $key a candidate WeightSourceCode value
     *
     * @return true if $key is in the list of known values for the type
     */
    public static function exists($key)
    {
        return in_array($key, self::getAll(), true);
    }
}
