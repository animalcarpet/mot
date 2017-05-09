<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Service;

use Csrf\CsrfConstants;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Guid\Guid;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request;
use Zend\Http\Response;

class LoginCsrfCookieService
{
    private $name;
    private $domain;
    private $path;
    private $secure;

    public function __construct(MotConfig $config)
    {
        $this->name = $config->withDefault(CsrfConstants::REQ_TOKEN)->get('csrf', 'cookie', 'name');
        $this->secure = $config->withDefault(true)->get('csrf', 'cookie', 'secure');
        $this->domain = $config->withDefault(null)->get('csrf', 'cookie', 'domain');
        $this->path = $config->withDefault(null)->get('csrf', 'cookie', 'path');
    }

    /**
     * @param Response $response
     *
     * @return string csrf token
     */
    public function addCsrfCookie(Response $response)
    {
        $token = Guid::newGuid();
        $cookie = new SetCookie(
            $this->name,
            $token,
            null,
            $this->path,
            $this->domain,
            $this->secure,
            true
        );

        $response->getHeaders()->addHeader($cookie);

        return $token;
    }

    /**
     * Verifies if token attached in cookie is equal to the token POSTed in the login form.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function validate(Request $request)
    {
        $requestToken = $request->getPost(CsrfConstants::REQ_TOKEN);

        /** @var array $cookies */
        $cookies = $request->getCookie();
        if (isset($cookies[$this->name])) {
            return $cookies[$this->name] === $requestToken;
        }

        return false;
    }
}
