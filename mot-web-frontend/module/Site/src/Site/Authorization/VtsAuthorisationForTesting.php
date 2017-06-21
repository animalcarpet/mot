<?php

namespace Site\Authorization;

use DvsaCommon\Enum\VehicleClassCode;

class VtsAuthorisationForTesting
{
    /**
     * @param array $vtsTestClasses
     * @return bool
     */
    public static function canTestClass1Or2($vtsTestClasses)
    {
        return is_array($vtsTestClasses)
            && (in_array(VehicleClassCode::CLASS_1, $vtsTestClasses) || in_array(VehicleClassCode::CLASS_2, $vtsTestClasses));
    }

    /**
     * @param array $vtsTestClasses
     * @return bool
     */
    public static function canTestAnyOfClass3AndAbove($vtsTestClasses)
    {
        $classes = [
            VehicleClassCode::CLASS_3,
            VehicleClassCode::CLASS_4,
            VehicleClassCode::CLASS_5,
            VehicleClassCode::CLASS_7,
        ];

        return is_array($vtsTestClasses) && (count(array_intersect($vtsTestClasses, $classes)) > 0);
    }
}