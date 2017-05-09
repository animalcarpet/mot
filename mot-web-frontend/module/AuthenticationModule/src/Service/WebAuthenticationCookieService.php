<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModule\Service;

use Dvsa\OpenAM\Options\OpenAMClientOptions;
use DvsaApplicationLogger\TokenService\TokenServiceInterface;
use Zend\Http\Header\SetCookie;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\Response;

/**
 * WebAuthenticationCookieService.
 */
class WebAuthenticationCookieService implements TokenServiceInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var \Dvsa\OpenAM\Options\OpenAMClientOptions
     */
    private $openAMOptions;

    /**
     * @param $request
     * @param $response
     * @param \Dvsa\OpenAM\Options\OpenAMClientOptions $openAMOptions
     */
    public function __construct($request, $response, OpenAMClientOptions $openAMOptions)
    {
        $this->request = $request;
        $this->response = $response;
        $this->openAMOptions = $openAMOptions;
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        $openAMCookieName = $this->openAMOptions->getCookieName();
        $cookies = $this->request->getCookie();

        return isset($cookies[$openAMCookieName]) ? $cookies[$openAMCookieName] : null;
    }

    /**
     * @param $token
     */
    public function setUpCookie($token)
    {
        $cookie = new SetCookie(
            $this->openAMOptions->getCookieName(),
            $token,
            null,
            $this->openAMOptions->getCookiePath(),
            $this->openAMOptions->getCookieDomain(),
            $this->openAMOptions->getCookieSecure(),
            $this->openAMOptions->getCookieHttpOnly()
        );

        $this->response->getHeaders()->addHeader($cookie);
    }

    public function tearDownCookie()
    {
        $cookie = new SetCookie(
            $this->openAMOptions->getCookieName(),
            null,
            strtotime('-1 Year', time()),
            $this->openAMOptions->getCookiePath(),
            $this->openAMOptions->getCookieDomain(),
            $this->openAMOptions->getCookieSecure(),
            $this->openAMOptions->getCookieHttpOnly()
        );

        $this->response->getHeaders()->addHeader($cookie);
    }
}
