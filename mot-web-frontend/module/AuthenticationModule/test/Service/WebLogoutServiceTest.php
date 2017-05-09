<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Service;

use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebAuthenticationCookieService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Log\LoggerInterface;
use Zend\Session\SessionManager;

class WebLogoutServiceTest extends \PHPUnit_Framework_TestCase
{
    const TOKEN = 'myToken';

    private $client;
    private $cookieService;
    private $logger;
    private $sessionManager;

    public function setUp()
    {
        $this->client = XMock::of(OpenAMClientInterface::class);
        $this->cookieService = XMock::of(WebAuthenticationCookieService::class);
        $this->sessionManager = XMock::of(SessionManager::class);
        $this->logger = XMock::of(LoggerInterface::class);
    }

    public function testLogout_givenToken_shouldLogoutFromOpenAM_and_shouldClearSessionStorage()
    {
        $this->cookieService
            ->expects($this->atLeastOnce())
            ->method('getToken')
            ->willReturn(self::TOKEN);

        $this->client
            ->expects($this->once())
            ->method('logout')
            ->with(self::TOKEN);

        $this->expectSessionClear();

        $this->createService()->logout();
    }

    public function testLogout_givenNoToken_shouldNotLogoutFromOpenAM_and_shouldClearSessionStorage()
    {
        $this->cookieService
            ->expects($this->atLeastOnce())
            ->method('getToken')
            ->willReturn(null);

        $this->client
            ->expects($this->never())
            ->method($this->anything());

        $this->expectSessionClear();

        $this->createService()->logout();
    }

    private function expectSessionClear()
    {
        $this->sessionManager
            ->expects($this->once())
            ->method('destroy')
            ->with(['clearStorage' => true]);
    }

    /**
     * @return WebLogoutService
     */
    private function createService()
    {
        return new WebLogoutService($this->client, $this->cookieService, $this->sessionManager, $this->logger);
    }
}
