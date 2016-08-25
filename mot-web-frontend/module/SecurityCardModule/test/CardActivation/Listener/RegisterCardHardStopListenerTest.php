<?php


use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Listener\RegisterCardHardStopListener;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardHardStopCondition;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\SimpleRouteStack;

class RegisterCardHardStopListenerTest extends PHPUnit_Framework_TestCase
{

    private $identityProvider;

    private $hardStopCondition;

    public function setUp()
    {
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $this->hardStopCondition = XMock::of(RegisterCardHardStopCondition::class);
    }

    public function testWhenRouteRestricted_shouldRedirect()
    {
        $this->withIdentity(new Identity());
        $this->withHardStop(true);

        $evt = $this->event('restricted');
        $this->listener()->__invoke($evt);

        $this->assertEquals(302, $evt->getResponse()->getStatusCode());
    }

    public function testWhenRouteNotRestricted_AndHardStop_shouldNotRedirect()
    {
        $this->withIdentity(new Identity());
        $this->withHardStop(true);

        $evt = $this->event('register-card');
        $this->listener()->__invoke($evt);

        $this->assertEquals(200, $evt->getResponse()->getStatusCode());
    }

    public function testWhenRouteRestricted_AndNoHardStop_shouldNotRedirect()
    {
        $this->withIdentity(new Identity());
        $this->withHardStop(false);

        $evt = $this->event('register-card');
        $this->listener()->__invoke($evt);

        $this->assertEquals(200, $evt->getResponse()->getStatusCode());
    }

    public function testWhenIdentityIsNotFound_shouldProceed()
    {
        $this->withIdentity(null);

        $evt = $this->event('someRoute');
        $this->listener()->__invoke($evt);

        $this->assertNotEquals(302, $evt->getResponse()->getStatusCode());
    }

    private function withHardStop($val)
    {
        $this->hardStopCondition->expects($this->any())
            ->method('isTrue')
            ->willReturn($val);
    }

    private function event($route)
    {
        $event = new MvcEvent();
        $routeStack = XMock::of(SimpleRouteStack::class);
        $routeStack->expects($this->any())->method('assemble')->willReturn('someUrl');
        $routeMatch = new RouteMatch([]);
        $event->setRouter($routeStack);
        $routeMatch->setMatchedRouteName($route);
        $event->setRouteMatch($routeMatch);
        $event->setResponse(new \Zend\Http\PhpEnvironment\Response());

        return $event;
    }


    private function withIdentity($identity)
    {
        $this->identityProvider->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);
    }

    private function listener()
    {
        return new RegisterCardHardStopListener($this->identityProvider, $this->hardStopCondition);
    }

}