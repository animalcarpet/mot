<?php

namespace DvsaCommon\Model;

use DvsaCommon\Enum\FuelTypeId;

class FuelTypeAndCylinderCapacity 
{
    /**
     * Return a list of fuel types which Cylinder Capacity is irrelevant to them
     * @return array
     */
    public static function getAllFuelTypeIdsWithOptionalCylinderCapacity()
    {
        return [
            FuelTypeId::ELECTRIC,
            FuelTypeId::FUEL_CELLS,
            FuelTypeId::STEAM,
        ];
    }

    /**
     * Return array of fuel type ids which Cylinder Capacity is required for them
     *
     * @return array
     */
    public static function getAllFuelTypeIdsWithCompulsoryCylinderCapacity()
    {
        return array_diff(
            FuelTypeId::getAll(),
            self::getAllFuelTypeIdsWithOptionalCylinderCapacity()
        );
    }

    /**
     * Return the list of fuel type ids which Cylinder Capacity is required for them in a string format
     * (Comma separated by default)
     *
     * @param string $delimiter
     * @return string
     */
    public static function getAllFuelTypeIdsWithCompulsoryCylinderCapacityAsString($delimiter = ',')
    {
        return implode(
            $delimiter,
            self::getAllFuelTypeIdsWithCompulsoryCylinderCapacity()
        );
    }

    /**
     * To check if CC is optional for the given fuel type.
     *
     * @param FuelTypeCode::getAll() $fuelType
     * @return bool
     */
    public static function isCylinderCapacityOptionalForFuelType($fuelType)
    {
        return in_array($fuelType, self::getAllFuelTypeIdsWithOptionalCylinderCapacity(), true);
    }

    /**
     * To check if CC is required for the given fuel type.
     *
     * @param FuelTypeCode::getAll() $fuelType
     * @return bool
     */
    public static function isCylinderCapacityCompulsoryForFuelType($fuelType)
    {
        return !self::isCylinderCapacityOptionalForFuelType($fuelType);
    }

}
