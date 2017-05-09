<?php

namespace Core\Service;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use Zend\Authentication\AuthenticationService;

/**
 * MotFrontendIdentityProviderInterface that delegates to Zend Framework.
 */
class MotFrontendIdentityProvider implements MotFrontendIdentityProviderInterface
{
    /** @var AuthenticationService $zendAuthenticationService */
    private $zendAuthenticationService;

    /**
     * @param AuthenticationService $zendAuthenticationService
     */
    public function setZendAuthenticationService($zendAuthenticationService)
    {
        $this->zendAuthenticationService = $zendAuthenticationService;
    }

    public function __construct(AuthenticationService $zendAuthenticationService)
    {
        $this->zendAuthenticationService = $zendAuthenticationService;
    }

    /** @return MotFrontendIdentityInterface */
    public function getIdentity()
    {
        if ($this->zendAuthenticationService->hasIdentity()) {
            return $this->zendAuthenticationService->getIdentity();
        } else {
            return null;
        }
    }
}
