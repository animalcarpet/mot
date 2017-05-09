<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Factory\Controller;

use Core\Action\RedirectToUrl;
use CoreTest\Controller\AbstractLightWebControllerTest;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\AuthenticationModule\Controller\SecurityController;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\IdentitySessionState;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\WebLoginResult;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\AuthenticationAccountLockoutViewModelBuilder;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\GotoUrlService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\IdentitySessionStateService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\LoginCsrfCookieService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\SuccessLoginResultRoutingService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLoginService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Authn\AuthenticationResultCode;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Stdlib\Parameters;

class SecurityControllerTest extends AbstractLightWebControllerTest
{
    const VALID_GOTO_URL = 'http://goto.url.com/url';
    const BASE_GOTO = 'http://goto.url.com';
    const INVALID_GOTO_URL = 'http://goto.com/aaa';
    const ACCESS_TOKEN = 'xifuvdv09RGEgrege';

    /** @var GotoUrlService */
    private $gotoService;

    /** @var IdentitySessionStateService $identitySessionStateService */
    private $identitySessionStateService;

    /** @var AuthenticationService $authenticationService */
    private $authenticationService;

    /** @var LoginCsrfCookieService $loginCsrfCookieService */
    private $loginCsrfCookieService;

    /** @var Request */
    private $request;

    /** @var Response */
    private $response;

    private $failureViewModelBuilder;

    /** @var WebLoginService $webLoginService */
    private $webLoginService;

    /** @var Identity $$identity */
    private $identity;

    private $featureToggle;

    /** @var SuccessLoginResultRoutingService */
    private $successLoginResultRoutingService;

    protected function setUp()
    {
        parent::setUp();

        $this->setController($this->buildController());
    }

    public function testOnGetLoginAction_notAuthenticated()
    {
        $this->givenGET();
        $this->identitySessionState(new IdentitySessionState(false, false));
        $this->loginCsrfCookieService->expects($this->once())->method('addCsrfCookie');

        $vm = $this->getController()->loginAction();
        $this->assertEquals('', $vm->getVariables['goto']);
        $this->assertEquals($vm->getTemplate(), 'authentication/login');
    }

    public function testOnGetLoginAction_whenAuthenticated_and_validGoto_shouldRedirectToGotoUrl()
    {
        $this->setController($this->buildController());

        $this->givenGET();
        $this->withValidGoto();

        $this->identitySessionState(new IdentitySessionState(true, false));
        $this->withGotoUrlAsQueryParam(self::VALID_GOTO_URL);
        $this->expectRedirectToUrl(self::VALID_GOTO_URL);
        $this->getController()->loginAction();
    }

    public function testOnGetLoginAction_whenAuthenticated_and_invalidGoto_shouldRedirectToGotoUrl()
    {
        $this->givenGET();
        $this->withValidGoto(false);

        $this->identitySessionState(new IdentitySessionState(true, false));
        $this->withGotoUrlAsQueryParam(self::INVALID_GOTO_URL);
        $this->expectRedirect(UserHomeController::ROUTE);
        $this->getController()->loginAction();
    }

    public function testOnGetLoginAction_whenNotAuthenticated_and_validGoto_shouldShowLoginPage()
    {
        $this->givenGET();
        $this->withValidGoto();

        $this->identitySessionState(new IdentitySessionState(false, false));
        $this->withGotoUrlAsQueryParam(self::VALID_GOTO_URL);

        $vm = $this->getController()->loginAction();

        $this->assertEquals('authentication/login', $vm->getTemplate());
        $this->assertEquals($this->gotoService->encodeGoto(self::VALID_GOTO_URL), $vm->getVariable('gotoUrl'));
    }

    public function testOnGetLoginAction_whenAuthenticated_and_toldToClearIdentity_shouldClearIdentity()
    {
        $this->givenGET();
        $this->identitySessionState(new IdentitySessionState(true, true));
        $this->authenticationService->expects($this->once())->method('clearIdentity');
        $this->expectRedirect(UserHomeController::ROUTE);

        $this->getController()->loginAction();
    }

    /** @dataProvider dataProvider_authnCodes */
    public function testOnPostLoginAction_givenAuthenticationFailure_shouldShowErrorOnScreen($authnCode)
    {
        $this->withValidPOST();
        $authenticationDto = (new WebLoginResult())->setCode($authnCode);
        $this->withLoginResult($authenticationDto);

        $this->assertEquals('authentication/login', $this->getController()->loginAction()->getTemplate());
    }

    public function testOnPostLoginAction_whenLoginSuccess_shouldRedirectInLineWithRoutingServiceResult()
    {
        $this->withValidPOST();
        $this->withValidGoto();

        $this->withLoggedInUser();
        $this->withRoutingServiceInvoked(new RedirectToUrl(self::VALID_GOTO_URL));

        $this->expectRedirectToUrl(self::VALID_GOTO_URL);
        $this->getController()->loginAction();
    }

    public function testOnPostLoginAction_givenLoginSuccess_and_failedCsrfValidation_shouldRedirectToLoginPage()
    {
        $this->withValidPOST();
        $this->loginCsrfCookieService->expects($this->once())->method('validate')->with($this->request)
            ->willReturn(false);
        $this->expectRedirect(SecurityController::ROUTE_LOGIN_GET);

        $this->getController()->loginAction();
    }

    public function testOnPostLogin_whenInvalidForm_shouldShowTheSamePageWithErrorForm()
    {
        $this->withInvalidPOST();

        $vm = $this->getController()->loginAction();
        $form = $vm->getVariables()['form'];
        $this->assertFalse($form->isValid());
        $this->assertEquals('authentication/login', $vm->getTemplate());
    }

    private function withValidLoginResult()
    {
        $authenticationDto = (new WebLoginResult())->setCode(AuthenticationResultCode::SUCCESS);
        $this->withLoginResult($authenticationDto);
    }

    private function withLoggedInUser()
    {
        $this->withValidLoginResult();
    }

    private function withRoutingServiceInvoked($returnObject)
    {
        $this->successLoginResultRoutingService
            ->expects($this->once())
            ->method('route')
         ->willReturn($returnObject);
    }

    private function withLoginResult(WebLoginResult $result)
    {
        $this->webLoginService->expects($this->once())->method('login')
            ->willReturn($result);
    }

    private function identitySessionState(IdentitySessionState $sessionState)
    {
        $this->identitySessionStateService->expects($this->atLeastOnce())
            ->method('getState')->willReturn($sessionState);
    }

    private function withGotoUrlAsQueryParam($url)
    {
        $this->request->setQuery(new Parameters(['goto' => $url]));
    }

    private function buildController()
    {
        $this->identitySessionStateService = XMock::of(IdentitySessionStateService::class);
        $this->authenticationService = XMock::of(AuthenticationService::class);
        $this->loginCsrfCookieService = XMock::of(LoginCsrfCookieService::class);
        $this->request = new Request();
        $this->response = new Response();
        $this->webLoginService = XMock::of(WebLoginService::class);
        $this->failureViewModelBuilder = XMock::of(AuthenticationAccountLockoutViewModelBuilder::class);
        $this->identity = XMock::of(Identity::class);
        $this->gotoService = XMock::of(GotoUrlService::class, ['isValidGoto']);
        $this->featureToggle = XMock::of(TwoFaFeatureToggle::class);
        $this->successLoginResultRoutingService = XMock::of(SuccessLoginResultRoutingService::class);

        $this
            ->authenticationService
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($this->identity);

        $controller = new SecurityController(
            $this->request,
            $this->response,
            $this->gotoService,
            $this->identitySessionStateService,
            $this->webLoginService,
            $this->loginCsrfCookieService,
            $this->authenticationService,
            $this->failureViewModelBuilder,
            $this->featureToggle,
            $this->successLoginResultRoutingService
        );

        return $controller;
    }

    private function withValidGoto($flag = true)
    {
        $this->gotoService->expects($this->any())->method('isValidGoto')->willReturn($flag);
    }

    public function givenGET()
    {
        $this->request->setMethod('GET');
    }

    public function withValidPOST()
    {
        $this->request->setMethod('POST');
        $this->request->setPost(new Parameters(['IDToken1' => 'username', 'IDToken2' => 'password']));
    }

    public function withInvalidPOST()
    {
        $this->request->setMethod('POST');
        $this->request->setPost(new Parameters(['IDToken1' => '', 'IDToken2' => '']));
    }

    public function dataProvider_authnCodes()
    {
        return [
            [AuthenticationResultCode::INVALID_CREDENTIALS],
            [AuthenticationResultCode::ERROR],
            [AuthenticationResultCode::UNRESOLVABLE_IDENTITY],
        ];
    }
}
