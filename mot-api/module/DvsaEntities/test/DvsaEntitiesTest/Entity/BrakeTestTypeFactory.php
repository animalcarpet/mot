<?php

namespace DvsaEntitiesTest\Entity;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaEntities\Entity\BrakeTestType;

/**
 * Creates BrakeTestType entity populated with given values.
 */
class BrakeTestTypeFactory
{
    /**
     * Creates BrakeTestType with given brake test type. Do not validate value. Can be passed everything.
     *
     * @param $brakeTestType
     *
     * @return BrakeTestType
     */
    public static function type($brakeTestType)
    {
        return (new BrakeTestType())->setCode($brakeTestType);
    }

    /**
     * Creates BrakeTestType with type = roller.
     *
     * @return BrakeTestType
     */
    public static function roller()
    {
        return self::type(BrakeTestTypeCode::ROLLER);
    }

    /**
     * Creates BrakeTestType with type = plate.
     *
     * @return BrakeTestType
     */
    public static function plate()
    {
        return self::type(BrakeTestTypeCode::PLATE);
    }

    /**
     * Creates BrakeTestType with type = decelerometer.
     *
     * @return BrakeTestType
     */
    public static function decelerometer()
    {
        return self::type(BrakeTestTypeCode::DECELEROMETER);
    }

    /**
     * Creates BrakeTestType with type = gradient.
     *
     * @return BrakeTestType
     */
    public static function gradient()
    {
        return self::type(BrakeTestTypeCode::GRADIENT);
    }

    /**
     * Creates BrakeTestType with type = floor.
     *
     * @return BrakeTestType
     */
    public static function floor()
    {
        return self::type(BrakeTestTypeCode::FLOOR);
    }
}
