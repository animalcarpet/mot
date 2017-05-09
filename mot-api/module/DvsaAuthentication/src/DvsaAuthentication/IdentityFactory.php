<?php

namespace DvsaAuthentication;

interface IdentityFactory
{
    /**
     * @param string    $username
     * @param string    $token
     * @param string    $uuid
     * @param \DateTime $passwordExpiryDate
     *
     * @return Identity
     */
    public function create($username, $token, $uuid, $passwordExpiryDate);
}
