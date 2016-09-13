<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\LostOrForgottenCard\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\AlreadyOrderedCardCookieService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\Header\Cookie;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Stdlib\Parameters;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;

class AlreadyOrderedCardCookieServiceTest extends \PHPUnit_Framework_TestCase
{
    const USER_ID = 105;

    private $motIdentityProvider;

    public function setUp()
    {
        $this->motIdentityProvider =  XMock::of(MotIdentityProviderInterface::class);
    }

    public function testCookieMatchesForUserShouldReturnTrue()
    {
        $this->withIdentity(self::USER_ID);
        $request = new Request();
        $request->setMethod('POST');
        $request->setPost(new Parameters([AlreadyOrderedCardCookieService::COOKIE_NAME . self::USER_ID => 1]));
        $request->getHeaders()->addHeader(new Cookie([AlreadyOrderedCardCookieService::COOKIE_NAME . self::USER_ID => 1]));

        $this->assertTrue($this->createService()->hasSeenOrderLandingPage($request));
    }

    public function testCookieForOtherUserDoesNotMatchReturnsFalse()
    {
        $this->withIdentity(7838918293);
        $request = new Request();
        $request->setMethod('POST');
        $request->setPost(new Parameters([AlreadyOrderedCardCookieService::COOKIE_NAME . self::USER_ID => 1]));
        $request->getHeaders()->addHeader(new Cookie([AlreadyOrderedCardCookieService::COOKIE_NAME . self::USER_ID => 1]));

        $this->assertFalse($this->createService()->hasSeenOrderLandingPage($request));
    }

    public function testCookieNotPresentValidateShouldReturnFalse()
    {
        $this->withIdentity(self::USER_ID);
        $request = new Request();
        $request->setMethod('POST');

        $this->assertFalse($this->createService()->hasSeenOrderLandingPage($request));
    }

    public function testCanCreateCookie()
    {
        $this->withIdentity(self::USER_ID);
        $response = new Response();

        $userPath = '/'. LostOrForgottenCardController::START_ROUTE;

        $this->createService()->addAlreadyOrderedCardCookie($response);

        /** @var SetCookie $setCookieHeader  */
        $setCookieHeader = $response->getCookie()[0];

        $this->assertEquals(AlreadyOrderedCardCookieService::COOKIE_NAME . self::USER_ID, $setCookieHeader->getName());
        $this->assertEquals($userPath, $setCookieHeader->getPath());
        $this->assertEquals(true, $setCookieHeader->isSecure());
    }

    private function withIdentity($userId)
    {
        $identity = new Identity();
        $identity
            ->setUserId($userId);

        $this->motIdentityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn($identity);
    }


    private function createService() {
        return new AlreadyOrderedCardCookieService(
            $this->motIdentityProvider
        );
    }
}