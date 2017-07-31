<?php

namespace DvsaEntitiesTest\Entity;

use DvsaCommon\Enum\WeightSourceCode;
use DvsaEntities\Entity\WeightSource;

/**
 * Creates WeightSourceFactory entity populated with given values.
 */
class WeightSourceFactory
{
    /**
     * Creates WeightSource with given source. Do not validate value. Can be passed everything.
     *
     * @param $weightSourceCode
     *
     * @return WeightSource
     */
    public static function type($weightSourceCode)
    {
        return (new WeightSource())->setCode($weightSourceCode);
    }

    /**
     * @return WeightSource with WeightSourceCode::VSI code
     */
    public static function vsi()
    {
        return self::type(WeightSourceCode::VSI);
    }

    /**
     * @return WeightSource with WeightSourceCode::PRESENTED code
     */
    public static function presented()
    {
        return self::type(WeightSourceCode::PRESENTED);
    }

    /**
     * @return WeightSource
     */
    public static function dgw()
    {
        return self::type(WeightSourceCode::DGW);
    }

    /**
     * @return WeightSource
     */
    public static function calculated()
    {
        return self::type(WeightSourceCode::CALCULATED);
    }

    /**
     * @return WeightSource
     */
    public static function mam()
    {
        return self::type(WeightSourceCode::MAM);
    }

}
