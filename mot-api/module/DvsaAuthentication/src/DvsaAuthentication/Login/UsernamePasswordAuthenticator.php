<?php

namespace DvsaAuthentication\Login;

use DvsaAuthentication\Login\Response\AuthenticationResponse;

/**
 * Base interface for all authenticators that use username and password to authenticate a user
 */
interface UsernamePasswordAuthenticator
{
    /**
     * @param $username
     * @param $password
     * @return AuthenticationResponse
     */
    public function authenticate($username, $password);

    /**
     * @param $username
     * @param $password
     * @return boolean
     */
    public function validateCredentials($username, $password);
}
