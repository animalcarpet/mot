<?php

namespace DvsaCommon\Model;

use DvsaCommon\Enum\FuelTypeCode;

class FuelType
{
    public static function getOrderedFuelTypeList()
    {
        return [
            FuelTypeCode::PETROL,
            FuelTypeCode::DIESEL,
            FuelTypeCode::ELECTRIC,
            FuelTypeCode::CNG,
            FuelTypeCode::ELECTRIC_DIESEL,
            FuelTypeCode::FUEL_CELLS,
            FuelTypeCode::GAS,
            FuelTypeCode::GAS_BI_FUEL,
            FuelTypeCode::GAS_DIESEL,
            FuelTypeCode::HYBRID_ELECTRIC_CLEAN,
            FuelTypeCode::LNG,
            FuelTypeCode::LPG,
            FuelTypeCode::STEAM,
            FuelTypeCode::OTHER,
        ];
    }
}