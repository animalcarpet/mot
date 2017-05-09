<?php

namespace DvsaAuthentication\Login\Response;

use DvsaAuthentication\Identity;
use DvsaCommon\Authn\AuthenticationResultCode;

/**
 * Represents successful authentication with authentication system.
 */
class AuthenticationSuccess extends AuthenticationResponse
{
    private $identity;

    public function __construct($identity)
    {
        $this->identity = $identity;
    }

    public function getCode()
    {
        return AuthenticationResultCode::SUCCESS;
    }

    public function getMessage()
    {
        return 'Authentication successful';
    }

    /** @return Identity */
    public function getIdentity()
    {
        return $this->identity;
    }
}
