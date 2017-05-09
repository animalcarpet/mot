<?php

namespace DvsaAuthentication\Login\Response;

use DvsaCommon\Authn\AuthenticationResultCode;

/**
 * Represents a failure upon accessing identity.
 */
class UnresolvableIdentityAuthenticationFailure extends AuthenticationResponse
{
    public function getCode()
    {
        return AuthenticationResultCode::UNRESOLVABLE_IDENTITY;
    }

    public function getMessage()
    {
        return 'Unresolvable identity error';
    }
}
