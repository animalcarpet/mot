<?php

namespace Application\Build;

/**
 * Generated php class
 *
 * Do not edit this file, your changes will be overwritten!!!
 *
 * Generated: Friday, June 12th, 2015, 7:29:45 PM
 */

class BuildConfig
{
    public static $buildId = '20150612070645';
    public static $environment = 'development';

    public static function getBuildId()
    {
        return self::$buildId;
    }

    public static function getEnvironment()
    {
        return self::$environment;
    }

    /**
     * Returns the current filename of the minified libraries file
     *
     * @return string
     */
    public static function getJavaScriptLibrariesFilename()
    {
        return "libraries.".self::getBuildId().".min.js";
    }

    /**
     * Returns the current filename of the enforcement file
     *
     * @return string
     */
    public static function getJavaScriptEnforcementFilename()
    {
        return "enforcement.".self::getBuildId().".js";
    }

    /**
     * Returns the current filename of the minified ie9 file
     *
     * @return string
     */
    public static function getJavaScriptLessThanIe9Filename()
    {
        return "lt.ie9.".self::getBuildId().".min.js";
    }
}