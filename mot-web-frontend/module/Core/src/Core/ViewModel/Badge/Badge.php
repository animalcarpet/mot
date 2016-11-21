<?php

namespace Core\ViewModel\Badge;

class Badge
{
    private $cssClass;

    public function __construct($cssClass)
    {
        $this->cssClass = $cssClass;
    }

    public function getCssClass()
    {
        return $this->cssClass;
    }

    /** @var Badge */
    private static $normal;

    /** @var Badge */
    private static $info;

    /** @var Badge */
    private static $alert;

    /** @var Badge */
    private static $warning;

    /** @var Badge */
    private static $success;

    public static function normal()
    {
        self::initializeIfNeeded();
        return self::$normal;
    }

    public static function info()
    {
        self::initializeIfNeeded();
        return self::$info;
    }

    public static function alert()
    {
        self::initializeIfNeeded();
        return self::$alert;
    }

    public static function warning()
    {
        self::initializeIfNeeded();
        return self::$warning;
    }

    public static function success()
    {
        self::initializeIfNeeded();
        return self::$success;
    }

    private static function initializeIfNeeded()
    {
        if (!self::$normal) {
            self::initialize();
        }
    }

    private static function initialize()
    {
        self::$normal = new Badge('badge');
        self::$alert = new Badge('badge--alert');
        self::$info = new Badge('badge--info');
        self::$warning = new Badge('badge--warn');
        self::$success = new Badge('badge--success');
    }
}
