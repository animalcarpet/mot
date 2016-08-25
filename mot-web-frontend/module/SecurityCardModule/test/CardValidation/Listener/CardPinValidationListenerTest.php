<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardValidation\Listener;

use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Listener\CardPinValidationListener;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use PHPUnit_Framework_TestCase;

use DvsaCommon\Auth\MotIdentityProviderInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Authentication\AuthenticationService;
use Core\Service\LazyMotFrontendAuthorisationService;
use Zend\Navigation\Page\Mvc;

class CardPinValidationListenerTest extends PHPUnit_Framework_TestCase
{
    const NOT_WHITELISTED_ROUTE = 'not-whitelisted';
    const WHITELISTED_ROUTE     = 'login';

    private $authenticationService;
    private $motIdentityProviderInterface;
    private $authorisationService;
    private $identity;
    private $featureToggle;

    public function setUp()
    {
        $this->authorisationService = XMock::of(LazyMotFrontendAuthorisationService::class);
        $this->motIdentityProviderInterface = XMock::of(MotIdentityProviderInterface::class);
        $this->authenticationService = XMock::of(AuthenticationService::class);
        $this->identity = XMock::of(Identity::class);
        $this->featureToggle = XMock::of(TwoFaFeatureToggle::class);

        $this
            ->authenticationService
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($this->identity);
    }

    public function testListenerShouldRedirectIfNonWhitelistedRoutesAndValid2faIdentity()
    {
        $listener = $this
            ->withHasFeatureToggle(true)
            ->withHasIdentity(true)
            ->withIdentityAuthenticatedWith2FA(false)
            ->withIdentitySecondFactorRequired(true)
            ->withIdentityHavingAuthenticateWith2faPermission(true)
            ->buildListener();

        $event = $this->buildMvcEvent(self::NOT_WHITELISTED_ROUTE);

        $listener($event);

        $this->assertRedirect($event);
    }

    public function testListenerShouldNotRedirectWhitelistedRoutes()
    {
        $listener = $this
            ->withHasFeatureToggle(true)
            ->withHasIdentity(true)
            ->withIdentityAuthenticatedWith2FA(false)
            ->withIdentitySecondFactorRequired(true)
            ->withIdentityHavingAuthenticateWith2faPermission(true)
            ->buildListener();

        $event = $this->buildMvcEvent(self::WHITELISTED_ROUTE);

        $listener($event);

        $this->assertNoRedirect($event);
    }

    public function testListenerShouldNotRedirectIfNoIdentity()
    {
        $listener = $this
            ->withHasFeatureToggle(true)
            ->withHasIdentity(false)
            ->withIdentityHavingAuthenticateWith2faPermission(false)
            ->buildListener();

        $event = $this->buildMvcEvent(self::NOT_WHITELISTED_ROUTE);

        $listener($event);

        $this->assertNoRedirect($event);
    }

    public function testListenerShouldNotRedirectIfNo2faPermission()
    {
        $listener = $this
            ->withHasFeatureToggle(true)
            ->withHasIdentity(true)
            ->withIdentityAuthenticatedWith2FA(false)
            ->withIdentitySecondFactorRequired(true)
            ->withIdentityHavingAuthenticateWith2faPermission(false)
            ->buildListener();

        $event = $this->buildMvcEvent(self::NOT_WHITELISTED_ROUTE);

        $listener($event);

        $this->assertNoRedirect($event);
    }

    public function testListenerShouldNotRedirectIfAlreadyAuthenticatedWith2fa()
    {
        $listener = $this
            ->withHasFeatureToggle(true)
            ->withHasIdentity(true)
            ->withIdentityAuthenticatedWith2FA(true)
            ->withIdentitySecondFactorRequired(true)
            ->withIdentityHavingAuthenticateWith2faPermission(true)
            ->buildListener();

        $event = $this->buildMvcEvent(self::NOT_WHITELISTED_ROUTE);

        $listener($event);

        $this->assertNoRedirect($event);
    }

    public function testListenerShouldNotRedirectIfSecondFactorNotRequired()
    {
        $listener = $this
            ->withHasFeatureToggle(true)
            ->withHasIdentity(true)
            ->withIdentityAuthenticatedWith2FA(false)
            ->withIdentitySecondFactorRequired(false)
            ->withIdentityHavingAuthenticateWith2faPermission(true)
            ->buildListener();

        $event = $this->buildMvcEvent(self::NOT_WHITELISTED_ROUTE);

        $listener($event);

        $this->assertNoRedirect($event);
    }

    public function testListenerShouldNotRedirectIfFeatureToggleIsOff()
    {
        $listener = $this
            ->withHasFeatureToggle(false)
            ->withHasIdentity(true)
            ->withIdentityAuthenticatedWith2FA(false)
            ->withIdentitySecondFactorRequired(true)
            ->withIdentityHavingAuthenticateWith2faPermission(true)
            ->buildListener();

        $event = $this->buildMvcEvent(self::NOT_WHITELISTED_ROUTE);

        $listener($event);

        $this->assertNoRedirect($event);
    }

    /**
     * @param bool $hasIdentity
     * @return Validate2FAPinListenerTest
     */
    private function withHasIdentity($hasIdentity)
    {
        $this->authenticationService
            ->expects($this->any())
            ->method('hasIdentity')
            ->willReturn($hasIdentity);

        return $this;
    }

    /**
     * @param bool $isSecondFactorRequired
     * @return Validate2FAPinListenerTest
     */
    private function withIdentitySecondFactorRequired($isSecondFactorRequired)
    {
        $this->identity
            ->expects($this->any())
            ->method('isSecondFactorRequired')
            ->willReturn($isSecondFactorRequired);

        return $this;
    }

    /**
     * @param bool $isAuthenticatedWith2FA
     * @return Validate2FAPinListenerTest
     */
    private function withIdentityAuthenticatedWith2FA($isAuthenticatedWith2FA)
    {
        $this->identity
            ->expects($this->any())
            ->method('isAuthenticatedWith2FA')
            ->willReturn($isAuthenticatedWith2FA);

        return $this;
    }

    /**
     * @param bool $hasPermission
     * @return Validate2FAPinListenerTest
     */
    private function withIdentityHavingAuthenticateWith2faPermission($hasPermission)
    {
        $this->authorisationService
            ->expects($this->any())
            ->method('isGranted')
            ->with(PermissionInSystem::AUTHENTICATE_WITH_2FA)
            ->willReturn($hasPermission);

        return $this;
    }

    private function buildListener()
    {
        return new CardPinValidationListener(
            $this->authenticationService,
            $this->motIdentityProviderInterface,
            $this->authorisationService,
            $this->featureToggle
        );
    }

    /**
     * @param string $routeName
     * @return MvcEvent
     */
    private function buildMvcEvent($routeName)
    {
        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName($routeName);

        $router = XMock::of(RouteStackInterface::class);
        $router
            ->expects($this->any())
            ->method('assemble')
            ->willReturn($routeName);

        $event = new MvcEvent();
        $event->setName(MvcEvent::EVENT_DISPATCH);
        $event->setResponse(new Response());
        $event->setRouter($router);
        $event->setRouteMatch($routeMatch);

        return $event;
    }

    private function withHasFeatureToggle($isFeatureToggleEnabled)
    {
        $this->featureToggle
            ->expects($this->any())
            ->method('isEnabled')
            ->willReturn($isFeatureToggleEnabled);

        return $this;
    }

    private function assertRedirect(MvcEvent $event)
    {
        $this->assertEquals(302, $event->getResponse()->getStatusCode());
    }

    private function assertNoRedirect(MvcEvent $event)
    {
        $this->assertEquals(200, $event->getResponse()->getStatusCode());
    }
}