<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardOrder\Controller;

use CoreTest\Controller\AbstractLightWebControllerTest;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardOrder;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\AlreadyOrderedNewCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use stdClass;

class AlreadyOrderedNewCardControllerTest extends AbstractLightWebControllerTest
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Identity
     */
    private $identity;

    /**
     * @var SecurityCardService
     */
    private $securityCardService;

    /**
     * @var TwoFaFeatureToggle
     */
    private $twoFaFeatureToggle;

    public function setUp()
    {
        parent::setUp();

        $this->request = new Request();
        $this->identity = XMock::of(Identity::class);
        $this->securityCardService = XMock::of(SecurityCardService::class);

        $this->withHasFeatureToggle(true);
    }

    public function testOnDispatch_when2faFeatureToggleIsOff_shouldRedirectToUserHome()
    {
        $this->withHasFeatureToggle(false);

        $controller = $this->buildController();

        $this->expectRedirect(UserHomeController::ROUTE);

        $controller->onDispatch($this->getMvcEventForIndexAction());
    }

    public function testIndexAction_whenNoSecurityCardOrdersAreFound_shouldRedirectToUserHome()
    {
        $this->withNoCardOrders();

        $controller = $this->buildController();

        $this->expectRedirect(UserHomeController::ROUTE);

        $controller->indexAction();
    }

    public function testIndexAction_whenSecurityCardOrdersAreFound_shouldDisplayCardOrderDate()
    {
        $expectedSubmittedOnDate = '2016-01-01 12:00:00';
        $this->withCardOrderSubmittedOn($expectedSubmittedOnDate);

        $controller = $this->buildController();

        $this->expectNoRedirect();

        $viewModel = $controller->indexAction();
        $this->assertEquals($expectedSubmittedOnDate, $viewModel->cardOrder->getSubmittedOn());
    }

    private function withCardOrderSubmittedOn($date)
    {
        $cardOrderData = new stdClass();
        $cardOrderData->submittedOn = $date;

        $this->securityCardService
            ->expects($this->any())
            ->method('getMostRecentSecurityCardOrderForUser')
            ->willReturn(new SecurityCardOrder($cardOrderData));

        return $this;
    }

    private function withNoCardOrders()
    {
        $this->securityCardService
            ->expects($this->any())
            ->method('getMostRecentSecurityCardOrderForUser')
            ->willReturn(null);

        return $this;
    }

    /**
     * @param bool $isFeatureToggleEnabled
     *
     * @return $this
     */
    private function withHasFeatureToggle($isFeatureToggleEnabled)
    {
        $this->twoFaFeatureToggle = new TwoFaFeatureToggle(
            new FeatureToggles([FeatureToggle::TWO_FA => $isFeatureToggleEnabled])
        );

        return $this;
    }

    /**
     * @return MvcEvent
     */
    private function getMvcEventForIndexAction()
    {
        return (new MvcEvent())->setRouteMatch(
            (new RouteMatch([]))->setParam('action', 'index')
        );
    }

    private function buildController()
    {
        $controller = new AlreadyOrderedNewCardController(
            $this->request,
            $this->identity,
            $this->securityCardService,
            $this->twoFaFeatureToggle
        );

        $this->setController($controller);
        $this->setUpPluginMocks();

        return $controller;
    }
}
