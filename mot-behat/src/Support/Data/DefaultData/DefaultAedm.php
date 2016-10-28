<?php

namespace Dvsa\Mot\Behat\Support\Data\DefaultData;

use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;

class DefaultAedm
{
    private static $aedm;

    /**
     * @return AuthenticatedUser
     */
    public static function get()
    {
        return self::$aedm;
    }

    /**
     * @param AuthenticatedUser $aedm
     */
    public static function set(AuthenticatedUser $aedm)
    {
        self::$aedm = $aedm;
    }
}
