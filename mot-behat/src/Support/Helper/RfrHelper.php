<?php

namespace Dvsa\Mot\Behat\Support\Helper;

class RfrHelper
{
    protected static $class3AndAboveRfr = [
        8455 => "Body condition" ,
    ];

    protected static $class1And2Rfr = [
        511 => "Performance, Gradient",
    ];

    public static function getRfrForClass3AndAboveById($id)
    {
        return self::$class3AndAboveRfr[$id];
    }

    public static function getRfrForClass3AndAboveByName($name)
    {
        return self::searchRfrByName($name, self::$class3AndAboveRfr);
    }

    public static function getRfrForClass1And2ById($id)
    {
        return self::$class1And2Rfr[$id];
    }

    public static function getRfrForClass1And2ByName($name)
    {
        return self::searchRfrByName($name, self::$class1And2Rfr);
    }

    private static function searchRfrByName($name, array $rfrList)
    {
        $rfr = array_search($name, $rfrList);
        if ($rfr === false) {
            return null;
        }

        return $rfr;
    }
}
