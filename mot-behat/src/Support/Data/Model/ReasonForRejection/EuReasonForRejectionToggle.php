<?php
namespace Dvsa\Mot\Behat\Support\Data\Model\ReasonForRejection;

class EuReasonForRejectionToggle
{
    private static $isEnabled = false;

    public static function enable()
    {
        self::$isEnabled = true;
    }

    public static function disable()
    {
        self::$isEnabled = false;
    }

    public static function isEnabled()
    {
        return self::$isEnabled;
    }
}
