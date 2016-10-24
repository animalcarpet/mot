<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardActivation\Service;

use Core\Service\LazyMotFrontendAuthorisationService;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCard;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardHardStopCondition;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardViewStrategy;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;

class RegisterCardViewStrategyTest extends \PHPUnit_Framework_TestCase
{
    private $featureToggles;

    private $hardStopCondition;

    private $identityProvider;

    private $authorisationService;

    /** @var  SecurityCardGuard */
    private $securityCardGuard;

    /** @var  PersonProfileGuardBuilder */
    private $personProfileGuardBuilder;

    /** @var  SecurityCardService */
    private $securityCardService;

    public function setUp()
    {
        $this->featureToggles = XMock::of(FeatureToggles::class);
        $this->hardStopCondition = XMock::of(RegisterCardHardStopCondition::class);
        $this->identityProvider = XMock::of(MotFrontendIdentityProviderInterface::class);
        $this->authorisationService = XMock::of(LazyMotFrontendAuthorisationService::class);
        $this->securityCardGuard = XMock::of(SecurityCardGuard::class);
        $this->personProfileGuardBuilder = XMock::of(PersonProfileGuardBuilder::class);
        $this->securityCardService = XMock::of(SecurityCardService::class);
    }

    private function with2FaToggle($ret)
    {
        $this->featureToggles->expects($this->any())
            ->method('isEnabled')
            ->willReturn($ret);
    }

    private function withHardStop($val)
    {
        $this->hardStopCondition->expects($this->any())
            ->method('isTrue')
            ->willReturn($val);
    }

    private function withIdentity($identity)
    {
        $this->identityProvider->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);
    }

    private function withPermissionToAuthenticateWith2Fa($val)
    {
        $this->authorisationService->expects($this->any())
            ->method('isGranted')->with(PermissionInSystem::AUTHENTICATE_WITH_2FA)
            ->willReturn($val);
    }

    public function testWhenHardStop_shouldDisplayEmptySubTitle()
    {
        $this->withHardStop(true);
        $this->assertEquals('', $this->strategy()->pageSubTitle());
    }

    public function testWhenNoHardStop_shouldDisplayProfileSubTitle()
    {
        $this->withHardStop(false);
        $this->assertEquals('Your profile', $this->strategy()->pageSubTitle());
    }

    public function testWhenHardStop_shouldDisplayProperBreadcrumbs()
    {
        $this->withHardStop(true);
        $this->assertEquals([
            ['Activate your security card' => '']
        ],
            $this->strategy()->breadcrumbs());
    }

    public function testWhenNoHardStop_shouldDisplayProperBreadcrumbs()
    {
        $this->withHardStop(false);
        $this->assertEquals([
            ['Your profile' => ContextProvider::YOUR_PROFILE_CONTEXT],
            ['Activate your security card' => '']
        ],
            $this->strategy()->breadcrumbs());
    }

    public function testWhenHardStop_shouldDisplayGoToSignInSkipCta()
    {
        $this->withHardStop(true);
        $this->assertEquals(
            '2fa/register-card/skip-cta/goToSignIn', $this->strategy()->skipCtaTemplate()
        );
    }

    public function testWhenNoHardStop_shouldDisplayGoToSignInSkipCta()
    {
        $this->withHardStop(false);
        $this->assertEquals(
            '2fa/register-card/skip-cta/goToProfile', $this->strategy()->skipCtaTemplate()
        );
    }

    public function testCanActivateACard_twoFactorEligibleUserAndHasAnActiveCard_shouldNotBeAbleToActivateACard()
    {
        $identity = new Identity();
        $identity->setUserId(999);

        $this->with2FaToggle(true);
        $this->withActiveTwoFaCard(true);
        $this->with2faEligibleUserWhichCanActivateACard(true);
        $this->withIdentity($identity);

        $actual = $this->strategy()->canActivateACard();

        $this->assertFalse($actual);
    }

    public function testCanActivateACard_twoFactorEligibleUserAndHasNoActiveCard_shouldBeAbleToActivateACard()
    {
        $identity = new Identity();
        $identity->setUserId(999);

        $this->with2FaToggle(true);
        $this->withActiveTwoFaCard(false);
        $this->with2faEligibleUserWhichCanActivateACard(true);
        $this->withIdentity($identity);

        $actual = $this->strategy()->canActivateACard();

        $this->assertTrue($actual);
    }

    public function testCanActivateACard_notTwoFactorEligibleUserAndHasActiveCard_shouldNotBeAbleToActivateACard()
    {
        $identity = new Identity();
        $identity->setUserId(999);

        $this->with2FaToggle(true);
        $this->withActiveTwoFaCard(true);
        $this->with2faEligibleUserWhichCanActivateACard(false);
        $this->withIdentity($identity);

        $actual = $this->strategy()->canActivateACard();

        $this->assertFalse($actual);
    }

    public function testCanActivateACard_notTwoFactorEligibleUserAndHasNoActiveCard_shouldNotBeAbleToActivateACard()
    {
        $identity = new Identity();
        $identity->setUserId(999);

        $this->with2FaToggle(true);
        $this->withActiveTwoFaCard(false);
        $this->with2faEligibleUserWhichCanActivateACard(false);
        $this->withIdentity($identity);

        $actual = $this->strategy()->canActivateACard();

        $this->assertFalse($actual);
    }

    private function with2faEligibleUserWhichCanActivateACard($value)
    {
        return $this->securityCardGuard
            ->expects($this->once())
            ->method('is2faEligibleUserWhichCanActivateACard')
            ->willReturn($value);
    }

    private function withActiveTwoFaCard($value)
    {
        return $this->securityCardGuard
            ->expects($this->once())
            ->method('hasActiveTwoFaCard')
            ->willReturn($value);
    }

    private function strategy()
    {
        return new RegisterCardViewStrategy(
            $this->featureToggles,
            $this->hardStopCondition,
            $this->authorisationService,
            $this->identityProvider,
            $this->securityCardGuard,
            $this->personProfileGuardBuilder
        );
    }
}