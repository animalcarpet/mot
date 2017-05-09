<?php

namespace DvsaAuthentication\Login\Response;

use DvsaCommon\Authn\AuthenticationResultCode;

/**
 * Represents other/unknown/expected error when contacting authentication system.
 */
class GenericAuthenticationFailure extends AuthenticationResponse
{
    private $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function getCode()
    {
        return AuthenticationResultCode::ERROR;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
