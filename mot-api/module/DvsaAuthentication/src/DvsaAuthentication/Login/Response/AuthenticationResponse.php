<?php

namespace DvsaAuthentication\Login\Response;

/**
 * Base class for all authentication responses.
 */
abstract class AuthenticationResponse
{
    private $extra;

    abstract public function getCode();

    abstract public function getMessage();

    public function getExtra()
    {
        return $this->extra;
    }

    protected function setExtra(array $extra)
    {
        $this->extra = $extra;
    }
}
