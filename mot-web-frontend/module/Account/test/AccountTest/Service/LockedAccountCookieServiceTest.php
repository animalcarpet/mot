<?php
namespace AccountTest\Service;

use Account\Service\LockedAccountCookieService;
use DvsaCommon\Date\DateUtils;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;

class LockedAccountCookieServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_setCookie()
    {
        $response = new Response();
        $sut = new LockedAccountCookieService($response, XMock::of(Request::class));
        $sut->setCookie();

        $cookies = $response->getHeaders()->toArray()["Set-Cookie"];
        $cookie = array_pop($cookies);

        $this->assertContains(LockedAccountCookieService::COOKIE_NAME, $cookie);
    }

    public function test_getTimeleft_returnsZero_whenCookieExpires()
    {
        $response = XMock::of(Response::class);
        $request = XMock::of(Request::class);

        $sut = new LockedAccountCookieService($response, $request);
        $timeLeft = $sut->getTimeLeftInMin();

        $this->assertEquals(0, $timeLeft);
    }

    public function test_getTimeleft_returnsCorrectTimeleft_whenCookieExists()
    {
        $response = XMock::of(Response::class);
        $request = XMock::of(Request::class);

        $expiryTime = 25;

        $cookieDateTime = DateUtils::nowAsUserDateTime();
        $cookieDateTime->add(new \DateInterval("PT" . $expiryTime . "M"));

        $request
            ->method("getCookie")
            ->willReturn([LockedAccountCookieService::COOKIE_NAME => $cookieDateTime->getTimestamp()]);

        $sut = new LockedAccountCookieService($response, $request);
        $timeleft = $sut->getTimeLeftInMin();

        $this->assertEquals($expiryTime, $timeleft);
    }
}
