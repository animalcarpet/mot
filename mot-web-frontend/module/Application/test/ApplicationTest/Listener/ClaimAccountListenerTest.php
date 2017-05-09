<?php

namespace ApplicationTest\Listener;

use Application\Listener\ClaimAccountListener;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface;

/**
 * Class ClaimAccountListenerTest.
 */
class ClaimAccountListenerTest extends PHPUnit_Framework_TestCase
{
    private $identityProvider;
    private $identity;

    public function setUp()
    {
        $this->identity = XMock::of(Identity::class);
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);
    }

    /**
     * @dataProvider routeListProvider
     */
    public function testDoNotRedirectIfUserGoToRouteInWhiteListOrInClaimAccountRouteList($route)
    {
        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName($route);

        $e = new MvcEvent();
        $e->setName(MvcEvent::EVENT_DISPATCH);
        $e->setResponse(new Response());
        $e->setRouteMatch($routeMatch);

        $claimAccountListener = new ClaimAccountListener($this->identityProvider);
        $claimAccountListener($e);

        $this->assertEquals(Response::STATUS_CODE_200, $e->getResponse()->getStatusCode());
    }

    public function testDoNotRedirectIfUserGoToRouteNotInWhiteListAndNotRequireClaimAccount()
    {
        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName('some-route');

        $e = new MvcEvent();
        $e->setName(MvcEvent::EVENT_DISPATCH);
        $e->setResponse(new Response());
        $e->setRouteMatch($routeMatch);

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

        $claimAccountListener = new ClaimAccountListener($this->identityProvider);
        $claimAccountListener($e);

        $this->assertEquals(Response::STATUS_CODE_200, $e->getResponse()->getStatusCode());
    }

    public function testRedirectIfUserGoToRouteNotInWhiteListAndRequireClaimAccount()
    {
        $routeMatch = XMock::of(RouteMatch::class);
        $routeMatch
            ->expects($this->atLeast(1))
            ->method('getMatchedRouteName')
            ->willReturn('some-route');

        $router = XMock::of(RouteStackInterface::class);
        $router
            ->expects($this->atLeast(1))
            ->method('assemble')
            ->willReturn('account/claim');

        $e = new MvcEvent();
        $e->setName(MvcEvent::EVENT_DISPATCH);
        $e->setResponse(new Response());
        $e->setRouteMatch($routeMatch);
        $e->setRouter($router);

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

        $claimAccountListener = new ClaimAccountListener($this->identityProvider);
        $claimAccountListener($e);

        $this->assertEquals(Response::STATUS_CODE_302, $e->getResponse()->getStatusCode());
    }

    public function testRedirectWhenUserGoToClaimAccountPageAndNotRequireClaimAccount()
    {
        $routeMatch = XMock::of(RouteMatch::class);
        $routeMatch
            ->expects($this->atLeast(1))
            ->method('getMatchedRouteName')
            ->willReturn('account/claim');

        $router = XMock::of(RouteStackInterface::class);
        $router
            ->expects($this->atLeast(1))
            ->method('assemble')
            ->willReturn('account/claim');

        $e = new MvcEvent();
        $e->setName(MvcEvent::EVENT_DISPATCH);
        $e->setResponse(new Response());
        $e->setRouteMatch($routeMatch);
        $e->setRouter($router);

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

        $claimAccountListener = new ClaimAccountListener($this->identityProvider);
        $claimAccountListener($e);

        $this->assertEquals(Response::STATUS_CODE_302, $e->getResponse()->getStatusCode());
    }

    public function routeListProvider()
    {
        return [
            ['login'],
            ['logout'],
            ['account/claim'],
            ['account/claim/confirmPassword'],
            ['account/claim/setSecurityQuestion'],
            ['account/claim/generatePin'],
            ['account/claim/reset'],
        ];
    }
}
