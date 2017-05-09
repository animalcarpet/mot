<?php

namespace DvsaAuthentication;

use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Authentication\AuthenticationService;

class IdentityProvider implements MotIdentityProviderInterface
{
    private $authenticationService;

    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /** @return Identity */
    public function getIdentity()
    {
        return $this->authenticationService->getIdentity();
    }
}
