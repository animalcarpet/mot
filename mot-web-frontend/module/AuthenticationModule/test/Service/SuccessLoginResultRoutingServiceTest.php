<?php

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Service;


use Core\Action\RedirectToRoute;
use Core\Action\RedirectToUrl;
use Core\Service\LazyMotFrontendAuthorisationService;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardOrder;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\WebLoginResult;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\GotoUrlService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\SuccessLoginResultRoutingService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardInformationController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Controller\RegisteredCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Controller\NewUserOrderCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Controller\RegisterCardInformationNewUserController;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Request;

class SuccessLoginResultRoutingServiceTest extends \PHPUnit_Framework_TestCase
{
    private $authorisationServiceClient;

    private $authorisationService;

    private $authenticationService;

    private $gotoUrlService;

    private $twoFaFeatureToggle;

    public function setUp()
    {
        $this->authorisationServiceClient = XMock::of(AuthorisationService::class);
        $this->authorisationService = XMock::of(LazyMotFrontendAuthorisationService::class);
        $this->authenticationService = XMock::of(AuthenticationService::class);
        $this->gotoUrlService = XMock::of(GotoUrlService::class);
        $this->twoFaFeatureToggle = XMock::of(TwoFaFeatureToggle::class);
    }

    public function test_when2FaOff_shouldRedirectToHomePage()
    {
        $this->with2Fa(false);

        /** @var RedirectToRoute $action */
        $action = $this->sut()->route(new WebLoginResult(), new Request());

        $this->assertEquals($action->getRouteName(), UserHomeController::ROUTE);
    }

    public function test_when2FaOn_AndUserHasDvsaRole_shouldRedirectToHomePage()
    {
        $this->with2Fa(true);
        $this->withDvsaUser(true);

        /** @var RedirectToRoute $action */
        $action = $this->sut()->route(new WebLoginResult(), new Request());

        $this->assertEquals($action->getRouteName(), UserHomeController::ROUTE);
    }

    public function test_when2FaOn_andUserHasAuthorisedFor2FARole_shouldRedirectTo2FaLoginScreen()
    {
        $this->with2Fa(true);
        $this->withDvsaUser(false);
        $this->with2FaAuthenticationPermission(true);
        $this->withIdentity($this->identity(true));

        /** @var RedirectToRoute $action */
        $action = $this->sut()->route($this->loginResult(), new Request());

        $this->assertEquals($action->getRouteName(), RegisteredCardController::ROUTE);
    }

    public function test_whenSecondFactorIsNotRequired_andUserHasTradeRole_shouldRedirectToAccountSecurityChangeInfoPage(
    )
    {
        $id = $this->identity(false);
        $this->with2Fa(true);
        $this->withDvsaUser(false);
        $this->with2FaAuthenticationPermission(true);
        $this->withIdentity($id);
        $this->withTradeUser(true);

        /** @var RedirectToRoute $action */
        $action = $this->sut()->route($this->loginResult(), new Request());

        $this->assertEquals(RegisterCardInformationController::REGISTER_CARD_INFORMATION_ROUTE,
            $action->getRouteName());
        $this->assertEquals(['userId' => $id->getUserId()], $action->getRouteParams());
    }

    public function test_whenSecondFactorIsNotRequired_andUserOrderedACard_shouldRedirectToNewUserAccountSecurityInfoPage()
    {
        $id = $this->identity(false);
        $this->with2Fa(true);
        $this->withDvsaUser(false);
        $this->with2FaAuthenticationPermission(true);
        $this->withIdentity($id);
        $this->withCardOrdered(true);

        /** @var RedirectToRoute $action */
        $action = $this->sut()->route($this->loginResult(), new Request());

        $this->assertEquals(RegisterCardInformationNewUserController::REGISTER_CARD_NEW_USER_INFORMATION_ROUTE,
            $action->getRouteName());
        $this->assertEquals(['userId' => $id->getUserId()], $action->getRouteParams());
    }

    public function test_whenSecondFactorIsNotRequired_andUserIsNewTesterWithNoCard_shouldRedirectToOrderCardInformationPage()
    {
        $id = $this->identity(false);
        $this->with2Fa(true);
        $this->withDvsaUser(false);
        $this->with2FaAuthenticationPermission(true);
        $this->withIdentity($id);
        $this->withCardOrdered(false);

        /** @var RedirectToRoute $action */
        $action = $this->sut()->route($this->loginResult(), new Request());

        $this->assertEquals(NewUserOrderCardController::ORDER_CARD_NEW_USER_ROUTE, $action->getRouteName());
        $this->assertEquals(['userId' => $id->getUserId()], $action->getRouteParams());
    }

    public function test_when2FaNotApplicable_andAppendedGotoUrlIsValid_shouldRedirectToGotoUrl()
    {
        $expectedUrl = 'someUrl';
        $this->with2Fa(false);
        $this->withGoToUrlValid($expectedUrl);

        /** @var RedirectToUrl $action */
        $action = $this->sut()->route($this->loginResult(), new Request());

        $this->assertEquals($expectedUrl, $action->getUrl());
    }

    private function sut()
    {
        return new SuccessLoginResultRoutingService(
            $this->authorisationServiceClient,
            $this->authenticationService,
            $this->authorisationService,
            $this->gotoUrlService,
            $this->twoFaFeatureToggle
        );
    }

    private function identity($isSecondFactorRequired)
    {
        $id = new Identity();
        $id->setUserId('1234335');
        $id->setSecondFactorRequired($isSecondFactorRequired);

        return $id;
    }

    private function loginResult()
    {
        return (new WebLoginResult())->setToken('TOKEN');
    }

    private function withGoToUrlValid($url)
    {
        $this->gotoUrlService->expects($this->atLeastOnce())->method('decodeGoto')->willReturn($url);
    }

    private function withIdentity($id)
    {
        $this->authenticationService->expects($this->any())->method('getIdentity')->willReturn($id);
    }

    private function with2FaAuthenticationPermission($hasPermission)
    {
        $this->authorisationService->expects($this->once())->method('isGranted')
            ->with(PermissionInSystem::AUTHENTICATE_WITH_2FA)->willReturn($hasPermission);
    }

    private function withDvsaUser($isDvsaUser)
    {
        $this->authorisationService->expects($this->once())->method('isDvsa')->willReturn($isDvsaUser);
    }

    private function withTradeUser($isTradeUser)
    {
        $this->authorisationService->expects($this->once())->method('isTradeUser')->willReturn($isTradeUser);
    }

    private function with2Fa($is2Fa)
    {
        $this->twoFaFeatureToggle->expects($this->once())->method('isEnabled')->willReturn($is2Fa);
    }

    private function withCardOrdered($hasOrdered)
    {
        $order = new \stdClass();
        $this->authorisationServiceClient->expects($this->once())->method('getSecurityCardOrders')
            ->willReturn(new Collection(($hasOrdered) ? [$order] : [], SecurityCardOrder::class));
    }
}
