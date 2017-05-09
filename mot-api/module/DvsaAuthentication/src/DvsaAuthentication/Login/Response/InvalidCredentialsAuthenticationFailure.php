<?php

namespace DvsaAuthentication\Login\Response;

use DvsaCommon\Authn\AuthenticationResultCode;

/**
 * Represents error occurred due to invalid credentials (e.g username and/or password).
 */
class InvalidCredentialsAuthenticationFailure extends AuthenticationResponse
{
    public function getCode()
    {
        return AuthenticationResultCode::INVALID_CREDENTIALS;
    }

    public function getMessage()
    {
        return 'Invalid Credentials';
    }
}
