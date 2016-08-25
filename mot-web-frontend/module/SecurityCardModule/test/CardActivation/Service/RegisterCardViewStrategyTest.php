<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardActivation\Service;

use Core\Service\LazyMotFrontendAuthorisationService;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardHardStopCondition;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardViewStrategy;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;

class RegisterCardViewStrategyTest extends \PHPUnit_Framework_TestCase
{
    private $featureToggles;

    private $hardStopCondition;

    private $identityProvider;

    private $authorisationService;


    public function setUp()
    {
        $this->featureToggles = XMock::of(FeatureToggles::class);
        $this->hardStopCondition = XMock::of(RegisterCardHardStopCondition::class);
        $this->identityProvider = XMock::of(MotFrontendIdentityProviderInterface::class);
        $this->authorisationService = XMock::of(LazyMotFrontendAuthorisationService::class);
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

    public function testWhen2FaToggleOn_andUserDoesNotRequire2Fa_and_hasPermissionToAuthenticateWith2Fa()
    {
        $this->with2FaToggle(true);
        $this->withIdentity((new Identity())->setSecondFactorRequired(false));
        $this->withPermissionToAuthenticateWith2Fa(true);

        $this->assertTrue($this->strategy()->canSee());
    }

    public function testWhen2FaToggleOff_andUserDoesNotRequire2Fa_and_hasPermissionToAuthenticateWith2Fa()
    {
        $this->with2FaToggle(false);
        $this->withIdentity((new Identity())->setSecondFactorRequired(false));
        $this->withPermissionToAuthenticateWith2Fa(true);

        $this->assertFalse($this->strategy()->canSee());
    }

    public function testWhen2FaToggleOn_andUserRequires2Fa_and_hasPermissionToAuthenticateWith2Fa()
    {
        $this->with2FaToggle(false);
        $this->withIdentity((new Identity())->setSecondFactorRequired(true));
        $this->withPermissionToAuthenticateWith2Fa(true);

        $this->assertFalse($this->strategy()->canSee());
    }

    public function testWhen2FaToggleOn_andUserDoesNotRequire2Fa_and_hasNoPermissionToAuthenticateWith2Fa()
    {
        $this->with2FaToggle(false);
        $this->withIdentity((new Identity())->setSecondFactorRequired(false));
        $this->withPermissionToAuthenticateWith2Fa(false);

        $this->assertFalse($this->strategy()->canSee());
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

    private function strategy()
    {
        return new RegisterCardViewStrategy(
            $this->featureToggles,
            $this->hardStopCondition,
            $this->authorisationService,
            $this->identityProvider
        );
    }
}