<?php

namespace DvsaCommonApiTest\Stub;

use DvsaAuthentication\Identity;

class IdentityStub extends Identity
{
    protected $username = null;

    public function __construct($username = null)
    {
        $this->username = $username;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
}
