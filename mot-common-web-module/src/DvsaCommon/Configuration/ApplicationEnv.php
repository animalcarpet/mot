<?php

namespace DvsaCommon\Configuration;

class ApplicationEnv
{
    const PRODUCTION = "production";
    const DEVELOPMENT = "development";

    public static function isDevelopmentEnv()
    {
        return self::getEnv() === self::DEVELOPMENT;
    }

    public static function isProductionEnv()
    {
       return (self::getEnv() === self::PRODUCTION || empty(self::getEnv()));
    }

    private static function getEnv()
    {
        return getenv('APPLICATION_ENV');
    }
}
