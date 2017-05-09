<?php

namespace Core\Action;

class FlashNamespace
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function equals(FlashNamespace $other)
    {
        return $this->name == $other->getName();
    }

    /** @var FlashNamespace */
    private static $error;

    /** @var FlashNamespace */
    private static $info;

    /** @var $success */
    private static $success;

    /** @var FlashNamespace */
    private static $warning;

    /** @var FlashNamespace */
    private static $default;

    public static function error()
    {
        self::initializeIfNeeded();

        return self::$error;
    }

    public static function info()
    {
        self::initializeIfNeeded();

        return self::$info;
    }

    public static function success()
    {
        self::initializeIfNeeded();

        return self::$success;
    }

    public static function warning()
    {
        self::initializeIfNeeded();

        return self::$warning;
    }

    public static function defaultNamespace()
    {
        self::initializeIfNeeded();

        return self::$default;
    }

    private static function initializeIfNeeded()
    {
        if (self::$default === null) {
            self::$error = new self('error');
            self::$info = new self('info');
            self::$success = new self('success');
            self::$warning = new self('warning');
            self::$default = new self('default');
        }
    }
}
