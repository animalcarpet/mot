<?php

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Service;

use Csrf\CsrfConstants;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\LoginCsrfCookieService;
use DvsaCommon\Configuration\MotConfig;
use Zend\Http\Header\Cookie;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Stdlib\Parameters;

class LoginCsrfCookieServiceTest extends \PHPUnit_Framework_TestCase
{
    private static $CONFIG = [
        'csrf' => [
                'cookie' => [
                        'name' => 'my_cookie',
                        'path' => '/my_path',
                        'domain' => '.my.domain.com',
                        'secure' => true,
                    ],
            ],
    ];

    private function createService()
    {
        return new LoginCsrfCookieService(new MotConfig(self::$CONFIG));
    }

    public function testAddCsrfCookie_givenRequest_and_defaults_shouldSetCookieAccrodingToDefaults()
    {
        $response = new Response();

        $service = new LoginCsrfCookieService(new MotConfig([]));

        $service->addCsrfCookie($response);
        /** @var SetCookie $setCookieHeader */
        $setCookieHeader = $response->getHeaders()->get('Set-Cookie')[0];

        $this->assertEquals('_csrf_token', $setCookieHeader->getName());
        $this->assertEquals(null, $setCookieHeader->getPath());
        $this->assertEquals(null, $setCookieHeader->getDomain());
        $this->assertEquals(true, $setCookieHeader->isSecure());
        $this->assertTrue($setCookieHeader->isHttponly());
        $this->assertTrue($setCookieHeader->isSessionCookie());
    }

    public function testAddCsrfCookie_givenRequest_shouldSetCookieInLineWithCsrfConfig()
    {
        $response = new Response();

        $service = $this->createService();
        $service->addCsrfCookie($response);
        /** @var SetCookie $setCookieHeader */
        $setCookieHeader = $response->getHeaders()->get('Set-Cookie')[0];

        $this->assertEquals('my_cookie', $setCookieHeader->getName());
        $this->assertEquals('/my_path', $setCookieHeader->getPath());
        $this->assertEquals('.my.domain.com', $setCookieHeader->getDomain());
        $this->assertEquals(true, $setCookieHeader->isSecure());
        $this->assertTrue($setCookieHeader->isHttponly());
        $this->assertTrue($setCookieHeader->isSessionCookie());
    }

    public function testValidate_givenPostParamAndCookieMatch_shouldReturnTrue()
    {
        $token = 'myToken';
        $request = new Request();
        $request->setMethod('POST');
        $request->setPost(new Parameters([CsrfConstants::REQ_TOKEN => $token]));
        $request->getHeaders()->addHeader(new Cookie(['my_cookie' => $token]));

        $this->assertTrue($this->createService()->validate($request));
    }

    public function testValidate_givenPostParamAndCookieDontMatch_shouldReturnFalse()
    {
        $requestToken = 'myToken';
        $cookieToken = 'cookieToken';
        $request = new Request();
        $request->setMethod('POST');
        $request->setPost(new Parameters([CsrfConstants::REQ_TOKEN => $requestToken]));
        $request->getHeaders()->addHeader(new Cookie(['my_cookie' => $cookieToken]));

        $this->assertFalse($this->createService()->validate($request));
    }
}
