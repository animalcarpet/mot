<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardActivation\Service;

use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardInformationController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardInformationCookieService;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\Header\Cookie;
use Zend\Http\Header\SetCookie;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Stdlib\Parameters;

class RegisterCardInformationCookieServiceTest extends \PHPUnit_Framework_TestCase
{
    private $identityProviderMock;

    public function setUp()
    {
        $this->identityProviderMock = XMock::of(MotFrontendIdentityProvider::class);
    }

    public function testCookieMatchesForUserShouldReturnTrue()
    {
        $userId = '105';
        $this->setUpAndMockIdentity($userId);

        $request = new Request();
        $request->setMethod('POST');
        $request->setPost(new Parameters([RegisterCardInformationCookieService::COOKIE_NAME => $userId]));
        $request->getHeaders()->addHeader(new Cookie([RegisterCardInformationCookieService::COOKIE_NAME => $userId]));

        $this->assertTrue($this->createService()->validate($request));
    }

    public function testCookieNotPresentValidateShouldReturnFalse()
    {
        $request = new Request();
        $request->setMethod('POST');

        $this->assertFalse($this->createService()->validate($request));
    }

    public function testCanCreateCookie()
    {
        $response = new Response();

        $userId = '105';
        $this->setUpAndMockIdentity($userId);

        $userPath = '/'.RegisterCardInformationController::REGISTER_CARD_INFORMATION_ROUTE.'/'.
            $userId;

        $this->createService()->addRegisterCardInformationCookie($response);

        /** @var SetCookie $setCookieHeader */
        $setCookieHeader = $response->getHeaders()->get('Set-Cookie')[0];

        $this->assertEquals(RegisterCardInformationCookieService::COOKIE_NAME, $setCookieHeader->getName());
        $this->assertEquals($userPath, $setCookieHeader->getPath());
        $this->assertEquals(true, $setCookieHeader->isSecure());
    }

    private function createService()
    {
        $registerCardInformationCookieService = new RegisterCardInformationCookieService(
            $this->identityProviderMock);

        return $registerCardInformationCookieService;
    }

    private function setUpAndMockIdentity($userId)
    {
        $identity = new Identity();
        $identity->setUserId($userId);

        $this->identityProviderMock->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);
    }
}
