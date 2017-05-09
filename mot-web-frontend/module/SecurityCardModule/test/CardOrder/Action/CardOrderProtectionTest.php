<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardOrder\Action;

use Core\Service\MotFrontendIdentityProvider;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderProtection;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\AlreadyOrderedNewCardController;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommonTest\TestUtils\XMock;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Core\Action\RedirectToRoute;

class CardOrderProtectionTest extends \PHPUnit_Framework_TestCase
{
    const USER_ID = 105;
    const SECONDARY_ID = 180;

    private $identityProvider;

    private $securityCardGuard;

    private $testerGroupAuthorisationMapper;

    private $twoFaFeatureToggle;

    public function setUp()
    {
        $this->identityProvider = XMock::of(MotFrontendIdentityProvider::class);
        $this->securityCardGuard = XMock::of(SecurityCardGuard::class);
        $this->testerGroupAuthorisationMapper = XMock::of(TesterGroupAuthorisationMapper::class);
        $this->twoFaFeatureToggle = XMock::of(TwoFaFeatureToggle::class);
    }

    public function testTwoFaTurnedOffRedirectsToHomePage()
    {
        $this->withFeatureToggle(false);

        /**
         * @var RedirectToRoute
         */
        $actual = $this->buildProtectionObject()->checkAuthorisation(self::USER_ID);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertEquals(UserHomeController::ROUTE, $actual->getRouteName());
    }

    public function testTwoFaOnAndCannotOrderCardForOtherUserRedirectsToHomePage()
    {
        $this->withFeatureToggle(true);
        $this->withIdentity();

        $this->withHasPermissionToOrderForOtherUser(false);

        /**
         * @var RedirectToRoute
         */
        $actual = $this->buildProtectionObject()->checkAuthorisation(self::SECONDARY_ID);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertEquals(UserHomeController::ROUTE, $actual->getRouteName());
    }

    public function testTwoFaOnAndCannotEnterJourneyAndHasOrderRedirectsToAlreadyOrderedPage()
    {
        $this->withFeatureToggle(true);
        $this->withIdentity();
        $this->withTesterAuthorisationMock();

        $this->withCanOrderSecurityCard(false);
        $this->withOutstandingCardOrders(true);

        /**
         * @var RedirectToRoute
         */
        $actual = $this->buildProtectionObject()->checkAuthorisation(self::USER_ID);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertEquals(AlreadyOrderedNewCardController::ROUTE, $actual->getRouteName());
    }

    public function testTwoFaOnAndCannotEnterJourneyAndHasNoOrderRedirectToHome()
    {
        $this->withFeatureToggle(true);
        $this->withIdentity();
        $this->withTesterAuthorisationMock();

        $this->withCanOrderSecurityCard(false);
        $this->withOutstandingCardOrders(false);

        /**
         * @var RedirectToRoute
         */
        $actual = $this->buildProtectionObject()->checkAuthorisation(self::USER_ID);
        $this->assertInstanceOf(RedirectToRoute::class, $actual);
        $this->assertEquals(UserHomeController::ROUTE, $actual->getRouteName());
    }

    public function testTwoFaOnAndUserCanEnterJourney()
    {
        $this->withFeatureToggle(true);
        $this->withIdentity();
        $this->withTesterAuthorisationMock();

        $this->withCanOrderSecurityCard(true);

        $actual = $this->buildProtectionObject()->checkAuthorisation(self::USER_ID);
        $this->assertNull($actual);
    }

    private function withFeatureToggle($turnedOn)
    {
        $this->twoFaFeatureToggle
            ->expects($this->any())
            ->method('isEnabled')
            ->willReturn($turnedOn);
    }

    private function withHasPermissionToOrderForOtherUser($hasPermission)
    {
        $this->securityCardGuard
            ->expects($this->once())
            ->method('hasPermissionToOrderCardForOtherUser')
            ->willReturn($hasPermission);
    }

    private function withTesterAuthorisationMock()
    {
        $this->testerGroupAuthorisationMapper
            ->expects($this->once())
            ->method('getAuthorisation')
            ->willReturn(new TesterAuthorisation());
    }

    private function withIdentity()
    {
        $identity = new Identity();
        $identity->setUserId(self::USER_ID);

        $this->identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);
    }

    private function withCanOrderSecurityCard($canOrderCard)
    {
        $this->securityCardGuard
            ->expects($this->once())
            ->method('canOrderSecurityCard')
            ->willReturn($canOrderCard);
    }

    private function withOutstandingCardOrders($outstandingOrders)
    {
        $this->securityCardGuard
            ->expects($this->once())
            ->method('hasOutstandingCardOrdersAndNoActiveCard')
            ->willReturn($outstandingOrders);
    }

    private function buildProtectionObject()
    {
        return new CardOrderProtection(
            $this->identityProvider,
            $this->securityCardGuard,
            $this->testerGroupAuthorisationMapper,
            $this->twoFaFeatureToggle
        );
    }
}
