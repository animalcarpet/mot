<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Listener;

use Dvsa\Mot\Frontend\AuthenticationModule\Listener\WebAuthenticationListener;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebAuthenticationCookieService;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Authentication\Storage\NonPersistent;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Log\Logger;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Mvc\MvcEvent;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;

class WebAuthenticationListenerTest extends \PHPUnit_Framework_TestCase
{
    const LOGOUT_URL = 'http://logout.url.com';
    private $authenticationService;
    private $tokenService;
    /**
     * @var MvcEvent
     */
    private $event;

    public function setUp()
    {
        $this->authenticationService = new AuthenticationService();
        $this->tokenService = XMock::of(WebAuthenticationCookieService::class);
        $this->event = new MvcEvent();
        $router = XMock::of(RouteStackInterface::class);
        $router->expects($this->any())->method('assemble')->willReturn(self::LOGOUT_URL);
        $this->event->setRouter($router);
        $this->event->setRequest(new Request());
        $this->event->setResponse(new Response());
    }

    public function testWhenUrlIsWhitelistedDoNothing()
    {
        $this->requestUriIs('http://myuri.com/login');
        $this->createListener()->__invoke($this->event);
        $this->setUpAuthenticationService($this->uncallableAdapter());

        $this->assertEquals(200, $this->event->getResponse()->getStatusCode());
    }

    public function testWhenRequestTokenSameAsSessionTokenDoNothing()
    {
        $requestToken = 'gg89r89g98er8ge';
        $this->requestUriIs('http://myuri.com/other');
        $this->setUpAuthenticationService($this->uncallableAdapter(), $requestToken);
        $this->requestTokenIs($requestToken);
        $this->createListener()->__invoke($this->event);

        $this->assertEquals(200, $this->event->getResponse()->getStatusCode());
    }

    public function testWhenRequestTokenDifferentThanSession_shouldReauthenticate()
    {
        $requestToken = 'gg89r89g98er8ge';
        $sessionToken = 'fregre9g90reg0re';
        $this->requestUriIs('http://myuri.com/other');
        $this->setUpAuthenticationService($this->adapterReturningResult(Result::SUCCESS), $sessionToken);
        $this->requestTokenIs($requestToken);

        $this->createListener()->__invoke($this->event);

        $this->assertEquals(200, $this->event->getResponse()->getStatusCode());
    }

    public function testWhenRequestTokenDifferentThanSession_and_reauthenticationFails_shouldRedirectToLogoutPage()
    {
        $requestToken = 'gg89r89g98er8ge';
        $sessionToken = 'fregre9g90reg0re';
        $this->requestUriIs('http://myuri.com/other');
        $this->setUpAuthenticationService($this->adapterReturningResult(Result::FAILURE), $sessionToken);

        $this->requestTokenIs($requestToken);
        $this->createListener()->__invoke($this->event);

        /** @var Response $response */
        $response = $this->event->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(self::LOGOUT_URL, $response->getHeaders()->get('Location')->getUri());
    }

    private function requestTokenIs($token)
    {
        $this->tokenService->expects($this->atLeastOnce())->method('getToken')->willReturn($token);
    }

    private function requestUriIs($url)
    {
        $this->event->getRequest()->setUri(new \Zend\Uri\Http($url));
    }

    private function adapterReturningResult($result)
    {
        $adapter = XMock::of(\Zend\Authentication\Adapter\AdapterInterface::class);
        $adapter
            ->expects($this->atLeastOnce())
            ->method('authenticate')
            ->willReturn(new Result($result, null));

        return $adapter;
    }

    private function uncallableAdapter()
    {
        $adapter = XMock::of(\Zend\Authentication\Adapter\AdapterInterface::class);
        $adapter->expects($this->never())->method($this->anything());

        return $adapter;
    }

    private function setUpAuthenticationService($adapter, $storedToken = null)
    {
        $storage = new NonPersistent();
        $data = (new Identity())->setAccessToken($storedToken);
        $storage->write($data);
        $this->authenticationService->setStorage($storage);
        $this->authenticationService->setAdapter($adapter);
    }

    /**
     * @return WebAuthenticationListener
     */
    private function createListener()
    {
        return new WebAuthenticationListener($this->authenticationService, $this->tokenService,
            XMock::of(Logger::class));
    }
}
