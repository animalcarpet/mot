<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\AuthenticationModule\Form\LoginForm;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\LoginLandingPage;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\WebLoginResult;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\AuthenticationAccountLockoutViewModelBuilder;
use Dvsa\Mot\Frontend\SecurityCardModule\Controller\NewUserOrderCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardInformationController;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\GotoUrlService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\IdentitySessionStateService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\LoginCsrfCookieService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLoginService;
use Dvsa\Mot\Frontend\SecurityCardModule\Controller\RegisterCardInformationNewUserController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Controller\RegisteredCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Authn\AuthenticationResultCode;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 *  SecurityController handles user's login requests. Every resource provided by this controller is not firewalled.
 */
class SecurityController extends AbstractDvsaActionController
{
    const FIELD_PASSWORD = 'IDToken2';
    const FIELD_USERNAME = 'IDToken1';
    const PAGE_TITLE = 'MOT testing service';
    const PARAM_GOTO = 'goto';
    const ROUTE_FORGOTTEN_PASSWORD = 'forgotten-password';
    const ROUTE_LOGIN_GET = 'login';
    const CREATE_ACCOUNT = 'account-register/create-an-account';

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var GotoUrlService
     */
    private $gotoService;

    /**
     * @var IdentitySessionStateService
     */
    private $identitySessionStateService;

    /**
     * @var LoginCsrfCookieService
     */
    private $loginCsrfCookieService;

    /** @var AuthenticationAccountLockoutViewModelBuilder */
    private $accountLocketViewModelBuilder;

    /** @var  TwoFaFeatureToggle */
    private $twoFaFeatureToggle;

    /**
     * @param Request $request
     * @param Response $response
     * @param GotoUrlService $loginGotoService
     * @param IdentitySessionStateService $identitySessionStateService
     * @param WebLoginService $loginService
     * @param LoginCsrfCookieService $loginCsrfCookieService
     * @param AuthenticationService $authenticationService
     * @param AuthenticationAccountLockoutViewModelBuilder $accountLocketViewModelBuilder
     * @param TwoFaFeatureToggle $twoFaFeatureToggle
     * @internal param AuthenticationAccountLockoutViewModelBuilder $failureViewModelBuilder
     */
    public function __construct(
        Request $request,
        Response $response,
        GotoUrlService $loginGotoService,
        IdentitySessionStateService $identitySessionStateService,
        WebLoginService $loginService,
        LoginCsrfCookieService $loginCsrfCookieService,
        AuthenticationService $authenticationService,
        AuthenticationAccountLockoutViewModelBuilder $accountLocketViewModelBuilder,
        TwoFaFeatureToggle $twoFaFeatureToggle

    ) {
        $this->request = $request;
        $this->response = $response;
        $this->gotoService = $loginGotoService;
        $this->identitySessionStateService = $identitySessionStateService;
        $this->loginService = $loginService;
        $this->loginCsrfCookieService = $loginCsrfCookieService;
        $this->accountLocketViewModelBuilder = $accountLocketViewModelBuilder;
        $this->authenticationService = $authenticationService;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
    }

    /**
     * @return ViewModel
     */
    public function loginAction()
    {
        $request = $this->request;
        if ($request->isPost()) {
            $response = $this->onPostLoginAction();
        } else {
            $response = $this->onGetLoginAction();
        }

        if ($response instanceof ViewModel) {
            $this->layout('layout/layout-govuk.phtml');
            $this->setPageTitle(self::PAGE_TITLE);
        }

        return $response;
    }

    /**
     * When a user accesses the login page several checks should happen to decide whether to display the login page or
     * redirect to the user home.
     *
     * @return ViewModel
     */
    public function onGetLoginAction()
    {
        /* @var Request $request */
        $request = $this->request;

        $state = $this->identitySessionStateService->getState();

        $rawGoto = $request->getQuery(self::PARAM_GOTO);
        if ($state->isAuthenticated()) {
            if ($state->shouldClearIdentity()) {
                $this->authenticationService->clearIdentity();
            }
            if ($this->gotoService->isValidGoto($rawGoto)) {
                return $this->redirect()->toUrl($rawGoto);
            } else {
                return $this->redirect()->toRoute(UserHomeController::ROUTE);
            }
        } else {
            $goto = $this->gotoService->encodeGoto($rawGoto);
            $viewVars = [
                'gotoUrl' => $goto,
                'form' => new LoginForm()
            ];
            return $this->buildLoginPageViewModel($viewVars);
        }
    }

    /**
     * @return ViewModel
     */
    public function onPostLoginAction()
    {
        $isCsrfTokenValid = $this->loginCsrfCookieService->validate($this->request);
        if (false === $isCsrfTokenValid) {
            return $this->redirect()->toRoute(self::ROUTE_LOGIN_GET);
        }
        $request = $this->request;
        $loginForm = new LoginForm();
        $postArray = $request->getPost()->toArray();
        $loginForm->setData($postArray);
        if (false === $loginForm->isValid()) {

            $loginForm->resetPassword();
            $viewVars = [
                'form' => $loginForm,
                'gotoUrl' => $request->getPost(self::PARAM_GOTO)
            ];
            return $this->buildLoginPageViewModel($viewVars);
        }

        /** @var WebLoginResult $result */
        $result = $this->loginService->login(
            $loginForm->getUsernameField()->getValue(),
            $loginForm->getPasswordField()->getValue()
        );

        if ($result->getCode() !== AuthenticationResultCode::SUCCESS) {
            return $this->showErrorOnAuthFail($result, $loginForm);
        }

        switch ($result->getTwoFaPage()) {
            case LoginLandingPage::LOG_IN_WITH_2FA:
                return $this->redirect()->toRoute(RegisteredCardController::ROUTE);

            case LoginLandingPage::ACTIVATE_2FA_EXISTING_USER:
                return $this->redirect()->toRoute(
                    RegisterCardInformationController::REGISTER_CARD_INFORMATION_ROUTE,
                    ['userId' => $this->authenticationService->getIdentity()->getUserId()]);

            case LoginLandingPage::ACTIVATE_2FA_NEW_USER:
                return $this->redirect()->toRoute(
                    RegisterCardInformationNewUserController::REGISTER_CARD_NEW_USER_INFORMATION_ROUTE,
                    ['userId' => $this->authenticationService->getIdentity()->getUserId()]);

            case LoginLandingPage::ORDER_2FA_NEW_USER:
                return $this->redirect()->toRoute(
                    NewUserOrderCardController::ORDER_CARD_NEW_USER_ROUTE,
                    ['userId' => $this->authenticationService->getIdentity()->getUserId()]);
        }

        $rawGoto = $request->getPost(self::PARAM_GOTO);
        $goto = $this->gotoService->decodeGoto($rawGoto);
        if ($goto) {
            return $this->redirect()->toUrl($goto);
        } else {
            return $this->redirect()->toRoute(UserHomeController::ROUTE);
        }
    }

    private function setUpLoginCsrfCookie(ViewModel $vm)
    {
        $csrfToken = $this->loginCsrfCookieService->addCsrfCookie($this->response);
        $vm->setVariable('csrfToken', $csrfToken);
    }

    private function buildLoginPageViewModel($vars)
    {

        $vm = new ViewModel($vars);
        $this->setUpLoginCsrfCookie($vm);
        $vm->setTemplate('authentication/login');
        $vm->setVariable('twoFaEnabled', $this->twoFaFeatureToggle->isEnabled());

        return $vm;
    }

    private function showErrorOnAuthFail($result, LoginForm $loginForm)
    {
        $resultCode = $result->getCode();
        if
        (
            $resultCode == AuthenticationResultCode::INVALID_CREDENTIALS ||
            $resultCode == AuthenticationResultCode::ERROR ||
            $resultCode == AuthenticationResultCode::UNRESOLVABLE_IDENTITY
        )
        {
            $loginForm->setAuthenticationFailError();
            $viewVars = [
                'form' => $loginForm,
                'gotoUrl' => $this->request->getPost(self::PARAM_GOTO),
                'authError' => true,
            ];

            return $this->buildLoginPageViewModel($viewVars);
        }

        return $this->accountLocketViewModelBuilder->createFromAuthenticationResponse($result);
    }
}
