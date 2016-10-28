<?php

namespace Dvsa\Mot\Behat\Support\Data\Model;

class Catalog
{
    const COLOURS = "colours";
    const COUNTRY_OF_REGISTRATION = "countryOfRegistration";
    const TRANSMISSION_TYPE = "transmissionType";

    const FIELD_ID = "id";
    const FIELD_NAME = "name";
    const FIELD_CODE = "code";

    private static $data = [];

    public static function set(array $data)
    {
        self::$data = $data;
    }

    public static function get($type)
    {
        return self::$data[$type];
    }
}