<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\Controller;

use CoreTest\Controller\AbstractLightWebControllerTest;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardInformationController;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommonTest\TestUtils\XMock;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardInformationCookieService;
use Core\Service\LazyMotFrontendAuthorisationService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Http\Request;
use Zend\Http\Response;
use DvsaCommon\Http\HttpStatus;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dashboard\Controller\UserHomeController;

class RegisterCardInformationControllerTest extends AbstractLightWebControllerTest
{
    const VALID_USER_ID = '105';
    const USER_ID_ROUTE_PARAM = 'userId';
    /**
     * @var RegisterCardInformationCookieService
     */
    private $cookieService;

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var LazyMotFrontendAuthorisationService
     */
    private $authorisationService;

    private $featureToggle;

    protected function setUp()
    {
        parent::setUp();
        $this->setController($this->buildController());
    }

    public function testUserIdFromParamsNullCauses404()
    {
        $this->setRouteParams([self::USER_ID_ROUTE_PARAM => null]);
        $this->controllerExpectsNotFound();
        $this->setUpAndMockIdentity(self::VALID_USER_ID, false);
        $this->getController()->registerCardInformationAction();
    }

    public function testDvsaUserCauses404()
    {
        $this->setRouteParams([self::USER_ID_ROUTE_PARAM => self::VALID_USER_ID]);
        $this->controllerExpectsNotFound();
        $this->setUpAndMockIdentity(self::VALID_USER_ID, false);
        $this->setUpIsDvsaUser(true);
        $this->getController()->registerCardInformationAction();
    }

    public function testActiveCardCauses404()
    {
        $this->setRouteParams([self::USER_ID_ROUTE_PARAM => self::VALID_USER_ID]);
        $this->controllerExpectsNotFound();
        $this->setUpAndMockIdentity(self::VALID_USER_ID, true);
        $this->setUpIsDvsaUser(false);
        $this->getController()->registerCardInformationAction();
    }

    public function testUserIdFromParamsNotUsersId()
    {
        $this->setRouteParams([self::USER_ID_ROUTE_PARAM => self::VALID_USER_ID]);
        $this->controllerExpectsNotFound();
        $this->setUpAndMockIdentity('110', false);
        $this->getController()->registerCardInformationAction();
    }

    public function testUserAlreadyHasCookieRedirectToHomePage()
    {
        $this->setRouteParams([self::USER_ID_ROUTE_PARAM => self::VALID_USER_ID]);
        $this->setUpAndMockIdentity(self::VALID_USER_ID, false);
        $this->mockCookieValidation(true);
        $this->setUpIsDvsaUser(false);
        $this->expectRedirect(UserHomeController::ROUTE);
        $this->getController()->registerCardInformationAction();
    }

    public function testUserDoesNotHaveCookieCardInformationPageShown()
    {
        $this->setRouteParams([self::USER_ID_ROUTE_PARAM => self::VALID_USER_ID]);
        $this->setUpAndMockIdentity(self::VALID_USER_ID, false);
        $this->mockCookieValidation(false);
        $this->setUpIsDvsaUser(false);
        $vm = $this->getController()->registerCardInformationAction();
        $this->assertEquals('2fa/register-card/register-card-information', $vm->getTemplate());
    }

    private function buildController()
    {
        $this->cookieService = XMock::of(RegisterCardInformationCookieService::class);
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $this->request = XMock::of(Request::class);
        $this->response = XMock::of(Response::class);
        $this->authorisationService = XMock::of(LazyMotFrontendAuthorisationService::class);
        $this->featureToggle = XMock::of(TwoFaFeatureToggle::class);
        $this->featureToggle->expects($this->any())->method('isEnabled')->willReturn(true);

        $controller = new RegisterCardInformationController(
            $this->cookieService,
            $this->request,
            $this->response,
            $this->identityProvider,
            $this->authorisationService,
            $this->featureToggle
        );

        return $controller;
    }

    private function controllerExpectsNotFound()
    {
        $this->response
            ->expects($this->once())
            ->method('setStatusCode')
            ->with(HttpStatus::HTTP_NOT_FOUND);

        return $this;
    }

    private function setUpAndMockIdentity($userId, $hasActiveCard)
    {
        $identity = new Identity();
        $identity
            ->setUserId($userId)
            ->setSecondFactorRequired($hasActiveCard);

        $this->identityProvider->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);
    }

    private function setUpIsDvsaUser($isDvsa)
    {
        $this->authorisationService->expects($this->any())
            ->method('isDvsa')
            ->willReturn($isDvsa);
    }

    private function mockCookieValidation($isValid)
    {
        $this->cookieService->expects($this->once())
            ->method('validate')
            ->willReturn($isValid);
    }
}
