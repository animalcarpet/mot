<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Model;


class WebLoginResult
{
    /** @var  string */
    private $code;

    /** @var  string */
    private $token;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return WebLoginResult
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return WebLoginResult
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }
}