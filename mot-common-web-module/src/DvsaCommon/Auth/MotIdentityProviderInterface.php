<?php

namespace DvsaCommon\Auth;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;

/**
 * Provides the current user's identity to objects that require it.
 */
interface MotIdentityProviderInterface
{
    /** @return MotFrontendIdentityInterface */
    public function getIdentity();
}
