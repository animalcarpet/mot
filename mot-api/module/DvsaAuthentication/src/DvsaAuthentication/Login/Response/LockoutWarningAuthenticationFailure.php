<?php

namespace DvsaAuthentication\Login\Response;

use DvsaCommon\Authn\AuthenticationResultCode;

/**
 * Represents a warning to a user that they have X attempts left before they will be locked out temporarily.
 */
class LockoutWarningAuthenticationFailure extends AuthenticationResponse
{
    const KEY_ATTEMPTS_LEFT = 'attemptsLeft';

    private $attemptsLeft;

    public function __construct($attemptsLeft)
    {
        $this->attemptsLeft = $attemptsLeft;
        $this->setExtra([self::KEY_ATTEMPTS_LEFT => $attemptsLeft]);
    }

    public function getCode()
    {
        return AuthenticationResultCode::LOCKOUT_WARNING;
    }

    public function getMessage()
    {
        return sprintf('Account lockout warning, attempts left: %s', $this->attemptsLeft);
    }
}
