<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardActivation\Service;

use Core\Service\LazyMotFrontendAuthorisationService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardHardStopCondition;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Zend\Di\ServiceLocator;
use Zend\EventManager\Exception\DomainException;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Parameters;
use Zend\View\Model\ViewModel;

class RegisterCardHardStopConditionTest extends \PHPUnit_Framework_TestCase
{
    private $featureToggles;

    private $authorisationService;

    private $identityProvider;

    public function setUp()
    {
        $this->featureToggles = XMock::of(FeatureToggles::class);
        $this->authorisationService = XMock::of(LazyMotFrontendAuthorisationService::class);
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);
    }

    private function withToggles($twoFa, $twoFaHardStop)
    {
        $this->featureToggles->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValueMap(
                [
                    [FeatureToggle::TWO_FA, $twoFa],
                    [FeatureToggle::TWO_FA_HARD_STOP, $twoFaHardStop]
                ]
            ));
    }

    private function withUserAsTradeUser($val)
    {
        $this->authorisationService->expects($this->any())
            ->method('isTradeUser')
            ->willReturn($val);
    }

    private function withIdentity($identity)
    {
        $this->identityProvider->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);
    }

    public function testWhen2FaToggleOn_andHardStopOn_andUserDoesNotRequire2Fa_and_userIsTradeUser_thenShouldRequireHardStop()
    {
        $this->withToggles(true, true);
        $this->withIdentity((new Identity())->setSecondFactorRequired(false));
        $this->withUserAsTradeUser(true);

        $this->assertTrue($this->condition()->isTrue());
    }

    public function testWhen2FaToggleOff_andHardStopOn_andUserDoesNotRequire2Fa_and_userIsTradeUser_thenShouldNotRequireHardStop()
    {
        $this->withToggles(false, true);
        $this->withIdentity((new Identity())->setSecondFactorRequired(false));
        $this->withUserAsTradeUser(true);

        $this->assertFalse($this->condition()->isTrue());
    }

    public function testWhen2FaToggleOn_andHardStopOff_andUserDoesNotRequire2Fa_and_userIsTradeUser_thenShouldNotRequireHardStop()
    {
        $this->withToggles(true, false);
        $this->withIdentity((new Identity())->setSecondFactorRequired(false));
        $this->withUserAsTradeUser(true);

        $this->assertFalse($this->condition()->isTrue());
    }

    public function testWhen2FaToggleOn_andHardStopOn_andUserRequires2Fa_and_userIsTradeUser_thenShouldNotRequireHardStop()
    {
        $this->withToggles(true, true);
        $this->withIdentity((new Identity())->setSecondFactorRequired(true));
        $this->withUserAsTradeUser(true);

        $this->assertFalse($this->condition()->isTrue());
    }

    public function testWhen2FaToggleOn_andHardStopOn_andUserDoesNotRequire2Fa_and_userIsNotTradeUser_thenShouldNotRequireHardStop()
    {
        $this->withToggles(true, true);
        $this->withIdentity((new Identity())->setSecondFactorRequired(false));
        $this->withUserAsTradeUser(false);

        $this->assertFalse($this->condition()->isTrue());
    }

    private function condition()
    {
        return new RegisterCardHardStopCondition(
            $this->featureToggles,
            $this->authorisationService,
            $this->identityProvider
        );
    }
}