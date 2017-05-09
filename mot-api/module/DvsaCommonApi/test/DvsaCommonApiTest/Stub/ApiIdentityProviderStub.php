<?php

namespace DvsaCommonApiTest\Stub;

use DvsaAuthentication\Identity;
use DvsaCommon\Auth\MotIdentityProviderInterface;

class ApiIdentityProviderStub implements MotIdentityProviderInterface
{
    private $identity;

    public function __construct(Identity $identity = null)
    {
        $this->identity = $identity;
    }

    public function setIdentity(Identity $identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /** @return Identity */
    public function getIdentity()
    {
        return $this->identity;
    }
}
