<?php

namespace DvsaCommonApiTest\Listener;

use DvsaCommonApi\Listener\ClaimAccountListener;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaAuthentication\Identity;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Zend\EventManager\EventManager;
use Zend\Http\PhpEnvironment\Response;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\View\Model\JsonModel;

/**
 * Class ClaimAccountListenerTest.
 */
class ClaimAccountListenerTest extends PHPUnit_Framework_TestCase
{
    private $identityProvider;
    private $identity;

    protected function setUp()
    {
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $this->identity = XMock::of(Identity::class);
    }

    public function testServiceExceptionOccurred()
    {
        $this
            ->identity
            ->expects($this->atLeast(1))
            ->method('isAccountClaimRequired')
            ->willReturn(true);

        $this
            ->identityProvider
            ->expects($this->atLeast(1))
            ->method('getIdentity')
            ->willReturn($this->identity);

        $eventManager = new EventManager();

        $claimAccountListener = new ClaimAccountListener($this->identityProvider);
        $claimAccountListener->attach($eventManager);

        $routeMatch = XMock::of(RouteMatch::class);
        $routeMatch
            ->expects($this->atLeast(1))
            ->method('getMatchedRouteName')
            ->willReturn('mot-retest');

        $e = new MvcEvent();
        $e->setResponse(new Response());
        $e->setRouteMatch($routeMatch);
        $e->setRequest(new Request());

        $claimAccountListener->invoke($e);

        $this->assertEquals(Response::STATUS_CODE_403, $e->getResponse()->getStatusCode());
        $this->assertInstanceOf(JsonModel::class, $e->getResult());
    }

    public function testNotThrowExceptionIfCalledEndpointIsInWhiteList()
    {
        $eventManager = new EventManager();

        $claimAccountListener = new ClaimAccountListener($this->identityProvider);
        $claimAccountListener->attach($eventManager);

        $routeMatch = XMock::of(RouteMatch::class);
        $routeMatch
            ->expects($this->atLeast(1))
            ->method('getMatchedRouteName')
            ->willReturn('session');

        $request = new Request();
        $request->setMethod('POST');

        $e = new MvcEvent();
        $e->setResponse(new Response());
        $e->setRouteMatch($routeMatch);
        $e->setRequest($request);

        $claimAccountListener->invoke($e);

        $this->assertEquals(Response::STATUS_CODE_200, $e->getResponse()->getStatusCode());
    }

    public function testNotThrowExceptionIfUserNotNeedClaimAccount()
    {
        $this
            ->identity
            ->expects($this->atLeast(1))
            ->method('isAccountClaimRequired')
            ->willReturn(false);

        $this
            ->identityProvider
            ->expects($this->atLeast(1))
            ->method('getIdentity')
            ->willReturn($this->identity);

        $eventManager = new EventManager();

        $claimAccountListener = new ClaimAccountListener($this->identityProvider);
        $claimAccountListener->attach($eventManager);

        $routeMatch = XMock::of(RouteMatch::class);
        $routeMatch
            ->expects($this->atLeast(1))
            ->method('getMatchedRouteName')
            ->willReturn('mot-retest');

        $e = new MvcEvent();
        $e->setResponse(new Response());
        $e->setRouteMatch($routeMatch);
        $e->setRequest(new Request());

        $claimAccountListener->invoke($e);

        $this->assertEquals(Response::STATUS_CODE_200, $e->getResponse()->getStatusCode());
    }
}
