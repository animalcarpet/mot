<?php
namespace Dvsa\Mot\Behat\Support\Data\Model;

class VehicleModelDictionary
{
    private static $data = [];

    public static function set(array $data)
    {
        self::$data = $data;
    }

    public static function get()
    {
        return self::$data;
    }
}
