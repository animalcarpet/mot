<?php

namespace DvsaCommon\Enum;

/**
 * Enum class generated from the 'fuel_type' table
 *
 * DO NOT EDIT! -- THIS CLASS IS GENERATED BY mot-common-web-module/generate_enums.php
 * @codeCoverageIgnore
 */
class FuelTypeCode
{
    const PETROL = 'PE';
    const DIESEL = 'DI';
    const ELECTRIC = 'EL';
    const STEAM = 'ST';
    const LPG = 'LP';
    const CNG = 'CN';
    const LNG = 'LN';
    const FUEL_CELLS = 'FC';
    const OTHER = 'OT';
    const GAS = 'GA';
    const GAS_BI_FUEL = 'GB';
    const HYBRID_ELECTRIC_CLEAN = 'HY';
    const GAS_DIESEL = 'GD';
    const ELECTRIC_DIESEL = 'ED';

    /**
     * @return array of values for the type FuelTypeCode
     */
    public static function getAll()
    {
        return [
            self::PETROL,
            self::DIESEL,
            self::ELECTRIC,
            self::STEAM,
            self::LPG,
            self::CNG,
            self::LNG,
            self::FUEL_CELLS,
            self::OTHER,
            self::GAS,
            self::GAS_BI_FUEL,
            self::HYBRID_ELECTRIC_CLEAN,
            self::GAS_DIESEL,
            self::ELECTRIC_DIESEL,
        ];
    }

    /**
     * @param mixed $key a candidate FuelTypeCode value
     *
     * @return true if $key is in the list of known values for the type
     */
    public static function exists($key)
    {
        return in_array($key, self::getAll(), true);
    }
}
