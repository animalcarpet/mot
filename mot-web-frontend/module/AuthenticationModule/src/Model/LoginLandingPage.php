<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Model;

abstract class LoginLandingPage
{
    const ACTIVATE_2FA_EXISTING_USER = 1;
    const LOG_IN_WITH_2FA = 2;
    const ACTIVATE_2FA_NEW_USER = 3;
    const ORDER_2FA_NEW_USER = 4;
}