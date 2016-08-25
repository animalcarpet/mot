<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardActivation\Action;

use Application\Model\RoleSummaryCollection;
use Core\Action\ActionResult;
use Core\Action\NotFoundActionResult;
use Core\Action\RedirectToRoute;
use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\ApiClient\Exception\ResourceNotFoundException;
use Dvsa\Mot\ApiClient\Exception\ResourceValidationException;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardPostAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardViewStrategy;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel\RegisterCardViewModel;
use DvsaCommon\Enum\RoleCode;
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
use Dvsa\Mot\Frontend\SecurityCardModule\Service\TwoFactorNominationNotificationService;

class RegisterCardPostActionTest extends \PHPUnit_Framework_TestCase
{
    private $viewStrategy;
    private $registerCardService;
    private $twoFactorNominationNotificationService;
    private $identityProvider;
    private $breadcrumbs = ['breadcrumbs'];
    private $skipCtaTemplate = 'someTemplate';
    private $subTitle = 'somePageSubtitle';
    private $serialNumber = 'STTA12345678';
    private $pin = '123456';
    private $request;

    public function setUp()
    {
        $this->viewStrategy = XMock::of(RegisterCardViewStrategy::class);
        $this->registerCardService = XMock::of(RegisterCardService::class);
        $this->twoFactorNominationNotificationService = XMock::of(TwoFactorNominationNotificationService::class);
        $this->identityProvider = XMock::of(MotFrontendIdentityProvider::class);
        $this->viewStrategy->expects($this->any())->method('breadcrumbs')->willReturn($this->breadcrumbs);
        $this->viewStrategy->expects($this->any())->method('skipCtaTemplate')->willReturn($this->skipCtaTemplate);
        $this->viewStrategy->expects($this->any())->method('pageSubTitle')->willReturn($this->subTitle);
        $this->withSuccessfulActivationCall();
        $this->request = new Request();
        $this->request->setPost(new Parameters(['serial_number' => 'STTA12345678', 'pin' => '123456']));

        $this->twoFactorNominationNotificationService
            ->expects($this->any())
            ->method('sendNotificationsForPendingNominations')
            ->willReturn(new RoleSummaryCollection([]));
        ;
    }

    public function testWhenFormInvalid_shouldReturnErrors()
    {
        $this->withCanSeePage(true);
        $this->request->setPost(new Parameters());
        /** @var ActionResult $result */
        $result = $this->action()->execute($this->request);


        $this->assertInstanceOf(ActionResult::class, $result);
        /** @var RegisterCardViewModel $vm */
        $vm = $result->getViewModel();
        $this->assertFalse($vm->getForm()->isValid());

        $this->assertEquals('2fa/register-card/register-card', $result->getTemplate());
        $this->assertEquals($this->subTitle, $result->layout()->getPageSubTitle());
        $this->assertEquals($this->skipCtaTemplate, $result->getViewModel()->getSkipCtaTemplate());
        $this->assertEquals($this->breadcrumbs, $result->layout()->getBreadcrumbs());
    }

    public function testWhenFormInvalid_shouldClearPin()
    {
        $this->withCanSeePage(true);
        $this->request->setPost(new Parameters());
        /** @var ActionResult $result */
        $result = $this->action()->execute($this->request);


        $this->assertInstanceOf(ActionResult::class, $result);
        /** @var RegisterCardViewModel $vm */
        $vm = $result->getViewModel();
        $this->assertEquals('', $vm->getForm()->getPinField()->getValue());

    }

    public function testWhenFormValid_shouldRedirectToSuccessPage()
    {
        $this->withCanSeePage(true);
        $this->withIdentity();

        /** @var RedirectToRoute $result */
        $result = $this->action()->execute($this->request);

        $this->assertInstanceOf(RedirectToRoute::class, $result);

        $this->assertEquals('register-card/success', $result->getRouteName());
    }

    public function testNominationServiceCalledAndRedirectToSuccess()
    {
        $this->withCanSeePage(true);
        $this->withIdentity();

        $this->twoFactorNominationNotificationService
            ->expects($this->once())
            ->method('sendNotificationsForPendingNominations')
        ;

        /** @var RedirectToRoute $result */
        $result = $this->action()->execute($this->request);

        $this->assertInstanceOf(RedirectToRoute::class, $result);

        $this->assertEquals('register-card/success', $result->getRouteName());
    }

    public function testWhenAedmNomination_NominationServiceCalledAndRedirectToSuccessWithNewRoleQueryParam()
    {
        $this->withCanSeePage(true);
        $this->withIdentity();

        $roleSummaryCollection = XMock::of(RoleSummaryCollection::class);
        $roleSummaryCollection
            ->expects($this->once())
            ->method('containsOrganisationRole')
            ->with('AEDM')
            ->willReturn(true);

        $this->twoFactorNominationNotificationService = XMock::of(TwoFactorNominationNotificationService::class);
        $this->twoFactorNominationNotificationService
            ->expects($this->once())
            ->method('sendNotificationsForPendingNominations')
            ->willReturn($roleSummaryCollection);

        /** @var RedirectToRoute $result */
        $result = $this->action()->execute($this->request);

        $this->assertInstanceOf(RedirectToRoute::class, $result);

        $this->assertEquals('register-card/success', $result->getRouteName());
        $this->assertEquals(['newlyAssignedRoles' => 'AEDM'], $result->getQueryParams());
    }

    public function testWhenActivationFailsDueInvalidSerialOrPin_shouldDisplayInvalidSerialOrPinMessage()
    {
        $this->withCanSeePage(true);

        $this->withFailingActivationCall(ResourceValidationException::class);
        /** @var ActionResult $result */
        $result = $this->action()->execute($this->request);

        $this->assertTrue($result->getViewModel()->isPinMismatch());
    }

    public function testWhenActivationFailsDueSerialNumberNotFound_shouldRaiseErrorForSerialNumber()
    {
        $this->withCanSeePage(true);

        $this->withFailingActivationCall(ResourceNotFoundException::class);
        /** @var ActionResult $result */
        $result = $this->action()->execute($this->request);

        $this->assertEquals("Enter a valid serial number",
            $result->getViewModel()->getForm()->getSerialNumberField()->getMessages()[0]
        );
    }

    private function withCanSeePage($val)
    {
        $this->viewStrategy->expects($this->any())->method('canSee')->willReturn($val);

    }

    private function withSuccessfulActivationCall()
    {
        $this->registerCardService->expects($this->any())->method('registerCard')->with($this->serialNumber,
            $this->pin);
    }

    private function withFailingActivationCall($exceptionClass)
    {
        $this->registerCardService->expects($this->any())->method('registerCard')
            ->willThrowException(XMock::of($exceptionClass));
    }

    private function withIdentity()
    {
        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn(new Identity((new Person())->setId(1)));
    }

    private function withPendingNominations($hasPendingNominations)
    {
        $this->twoFactorNominationNotificationService
            ->expects($this->once())
            ->method('hasPendingNominations')
            ->wilLReturn($hasPendingNominations);
    }

    private function action()
    {
        return new RegisterCardPostAction(
            $this->viewStrategy,
            $this->registerCardService,
            $this->twoFactorNominationNotificationService,
            $this->identityProvider
        );
    }
}
