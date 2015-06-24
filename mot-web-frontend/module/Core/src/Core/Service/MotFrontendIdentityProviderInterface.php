<?php

namespace Core\Service;

use DvsaAuthentication\Model\MotFrontendIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Authentication\AuthenticationService;

/**
 * MotIdentityProviderInterface that delegates to Zend Framework
 */
interface MotFrontendIdentityProviderInterface extends  MotIdentityProviderInterface
{
    /** @return MotFrontendIdentityInterface */
    public function getIdentity();
}