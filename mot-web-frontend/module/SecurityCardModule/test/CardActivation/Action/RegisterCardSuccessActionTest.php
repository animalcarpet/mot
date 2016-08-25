<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardActivation\Action;

use Core\Action\ActionResult;
use Core\Action\NotFoundActionResult;
use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardSuccessAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardViewStrategy;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel\RegisterCardSuccessViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\TwoFactorNominationNotificationService;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
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

class RegisterCardSuccessActionTest extends \PHPUnit_Framework_TestCase
{
    private $registerCardService;
    private $viewStrategy;
    private $twoFactorNominationNotificationService;
    private $identityProvider;
    private $breadcrumbs = ['breadcrumbs'];

    public function setUp()
    {
        $this->registerCardService = XMock::of(RegisterCardService::class);
        $this->viewStrategy = XMock::of(RegisterCardViewStrategy::class);
        $this->twoFactorNominationNotificationService = XMock::of(TwoFactorNominationNotificationService::class);
        $this->identityProvider = XMock::of(MotFrontendIdentityProvider::class);
        $this->viewStrategy->expects($this->any())->method('breadcrumbs')->willReturn($this->breadcrumbs);
    }

    private function withUserRegistered($val)
    {
        $this->registerCardService->expects($this->any())
            ->method('isUserRegistered')
            ->willReturn($val);
    }

    public function testWhenUserRegistered_shouldShowSuccessPage()
    {
        $this->withUserRegistered(true);
        $this->withIdentity();
        $result = $this->action()->execute(new Request());
        $this->assertInstanceOf(ActionResult::class, $result);
        $this->assertEquals('2fa/register-card/success', $result->getTemplate());
    }

    public function testWhenUserRegisteredAndHasNewAedmRole_shouldShowSuccessPage()
    {
        $this->withUserRegistered(true);
        $this->withIdentity();

        $request = new Request();
        $request->setQuery(new Parameters(['newlyAssignedRoles' => 'AEDM']));

        $result = $this->action()->execute($request);

        /** @var RegisterCardSuccessViewModel $viewModel */
        $viewModel = $result->getViewModel();
        $this->assertTrue($viewModel->getHasNewAedmRole());
    }

    public function testWhenUserNotRegistered_shouldNotShowSuccessPage()
    {
        $this->withUserRegistered(false);
        $result = $this->action()->execute(new Request());
        $this->assertInstanceOf(NotFoundActionResult::class, $result);
    }

    private function withIdentity()
    {
        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn(new Identity((new Person())->setId(1)));
    }

    private function action()
    {
        return new RegisterCardSuccessAction(
            $this->registerCardService,
            $this->viewStrategy,
            $this->twoFactorNominationNotificationService,
            $this->identityProvider
        );
    }
}
