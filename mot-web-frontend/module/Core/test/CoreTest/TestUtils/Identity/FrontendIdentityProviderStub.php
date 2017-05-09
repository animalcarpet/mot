<?php

namespace CoreTest\TestUtils\Identity;

use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;

class FrontendIdentityProviderStub implements MotFrontendIdentityProviderInterface
{
    private $identity;

    public function setIdentity(Identity $identity)
    {
        $this->identity = $identity;
    }

    /** @return Identity */
    public function getIdentity()
    {
        return $this->identity;
    }
}
