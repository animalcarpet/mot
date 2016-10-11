<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardValidation\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\AlreadyLoggedInTodayWithLostForgottenCardCookieService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\Header\Cookie;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Stdlib\Parameters;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;

class AlreadyLoggedInTodayWithLostForgottenCardCookieServiceTest extends \PHPUnit_Framework_TestCase
{
    const USER_ID = 105;

    /** @var MotIdentityProviderInterface $identityProvider*/
    private $motIdentityProvider;

    const COOKIE_DATE = '2016-10-05 14:39:42';
    const ACTIVATION_DATE = '2016-10-05 14:40:42';

    public function setUp()
    {
        $this->motIdentityProvider =  XMock::of(MotIdentityProviderInterface::class);
    }

    public function testCookieMatchesForUserShouldReturnTrue()
    {
        $this->withIdentity(self::USER_ID);
        $request = new Request();
        $request->setMethod('POST');
        $request->setPost(new Parameters([AlreadyLoggedInTodayWithLostForgottenCardCookieService::COOKIE_NAME . self::USER_ID => 1]));
        $request->getHeaders()->addHeader(new Cookie([AlreadyLoggedInTodayWithLostForgottenCardCookieService::COOKIE_NAME . self::USER_ID => 1]));

        $this->assertTrue($this->createService()->hasLoggedInTodayWithLostForgottenCardJourney($request));
    }

    public function testCookieForOtherUserDoesNotMatchReturnsFalse()
    {
        $this->withIdentity(7838918293);
        $request = new Request();
        $request->setMethod('POST');
        $request->setPost(new Parameters([AlreadyLoggedInTodayWithLostForgottenCardCookieService::COOKIE_NAME . self::USER_ID => 1]));
        $request->getHeaders()->addHeader(new Cookie([AlreadyLoggedInTodayWithLostForgottenCardCookieService::COOKIE_NAME . self::USER_ID => 1]));

        $this->assertFalse($this->createService()->hasLoggedInTodayWithLostForgottenCardJourney($request));
    }

    public function testManyCookiesForOtherUsersDoesNotMatchReturnsFalse()
    {
        $this->withIdentity(7838918293);
        $request = new Request();
        $request->setMethod('POST');
        $request->setPost(new Parameters([AlreadyLoggedInTodayWithLostForgottenCardCookieService::COOKIE_NAME . self::USER_ID => 1]));
        $cookies = array();
        for ($userId = 1; $userId <= 30; $userId++)
        {
            $cookies[AlreadyLoggedInTodayWithLostForgottenCardCookieService::COOKIE_NAME . $userId] = 1;
        }
        $request->getHeaders()->addHeader(new Cookie($cookies));

        $this->assertFalse($this->createService()->hasLoggedInTodayWithLostForgottenCardJourney($request));
    }

    public function testManyCookiesForOtherUsersFindMatchingCookieAndReturnsTrue()
    {
        $this->withIdentity(25);
        $request = new Request();
        $request->setMethod('POST');
        $request->setPost(new Parameters([AlreadyLoggedInTodayWithLostForgottenCardCookieService::COOKIE_NAME . self::USER_ID => 1]));
        $cookies = array();
        for ($userId = 1; $userId <= 30; $userId++)
        {
            $cookies[AlreadyLoggedInTodayWithLostForgottenCardCookieService::COOKIE_NAME . $userId] = 1;
        }
        $request->getHeaders()->addHeader(new Cookie($cookies));

        $this->assertTrue($this->createService()->hasLoggedInTodayWithLostForgottenCardJourney($request));
    }

    public function testCookieNotPresentValidateShouldReturnFalse()
    {
        $this->withIdentity(self::USER_ID);
        $request = new Request();
        $request->setMethod('POST');

        $this->assertFalse($this->createService()->hasLoggedInTodayWithLostForgottenCardJourney($request));
    }

    public function testCanCreateCookie()
    {
        $this->withIdentity(self::USER_ID);
        $response = new Response();

        $userPath = '/';

        $this->createService()->addLoggedInViaLostForgottenCardCookie($response);

        /** @var SetCookie $setCookieHeader  */
        $setCookieHeader = $response->getCookie()[0];

        $this->assertEquals(AlreadyLoggedInTodayWithLostForgottenCardCookieService::COOKIE_NAME . self::USER_ID, $setCookieHeader->getName());
        $this->assertEquals($userPath, $setCookieHeader->getPath());
        $this->assertEquals(true, $setCookieHeader->isSecure());
    }

    public function testActivationOccouredAfterCookieWithNewerActivation()
    {
        $this->withIdentity(self::USER_ID);
        $request = new Request();
        $request->setMethod('POST');
        $request->setPost(new Parameters([AlreadyLoggedInTodayWithLostForgottenCardCookieService::COOKIE_NAME . self::USER_ID => self::COOKIE_DATE]));
        $request->getHeaders()->addHeader(new Cookie([AlreadyLoggedInTodayWithLostForgottenCardCookieService::COOKIE_NAME . self::USER_ID => self::COOKIE_DATE]));

        $activationDate =  new \DateTime(self::ACTIVATION_DATE, new \DateTimeZone('Europe/London'));

        $this->assertTrue($this->createService()->hasActivationOccouredAfterCookie($request, $activationDate));
    }

    public function testActivationDidNotOccourAfterCookie()
    {
        $this->withIdentity(self::USER_ID);
        $request = new Request();
        $request->setMethod('POST');
        $request->setPost(new Parameters([AlreadyLoggedInTodayWithLostForgottenCardCookieService::COOKIE_NAME . self::USER_ID => self::ACTIVATION_DATE]));
        $request->getHeaders()->addHeader(new Cookie([AlreadyLoggedInTodayWithLostForgottenCardCookieService::COOKIE_NAME . self::USER_ID => self::ACTIVATION_DATE]));

        $activationDate =  new \DateTime(self::COOKIE_DATE, new \DateTimeZone('Europe/London'));

        $this->assertFalse($this->createService()->hasActivationOccouredAfterCookie($request, $activationDate));
    }

    private function withIdentity($userId)
    {
        $identity = new Identity();
        $identity
            ->setUserId($userId);

        $this->motIdentityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);
    }

    private function createService() {
        return new AlreadyLoggedInTodayWithLostForgottenCardCookieService(
            $this->motIdentityProvider
        );
    }
}