<?php

namespace DvsaAuthentication\Login\Response;

use DvsaCommon\Authn\AuthenticationResultCode;

/**
 * Represents a failure due to account being locked temporarily or permanently.
 */
class AccountLockedAuthenticationFailure extends AuthenticationResponse
{
    public function getCode()
    {
        return AuthenticationResultCode::ACCOUNT_LOCKED;
    }

    public function getMessage()
    {
        return 'Account locked';
    }
}
