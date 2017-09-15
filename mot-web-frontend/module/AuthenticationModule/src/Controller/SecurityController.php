<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\AuthenticationModule\Form\LoginForm;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\WebLoginResult;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\AuthenticationAccountLockoutViewModelBuilder;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\SuccessLoginResultRoutingService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\GotoUrlService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\IdentitySessionStateService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\LoginCsrfCookieService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLoginService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Authn\AuthenticationResultCode;
use Zend\Authentication\AuthenticationService;
use Zend\Form\ElementInterface;
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

    /** @var TwoFaFeatureToggle */
    private $twoFaFeatureToggle;

    /** @var SuccessLoginResultRoutingService */
    private $successLoginResultRoutingService;

    /**
     * @param Request                                      $request
     * @param Response                                     $response
     * @param GotoUrlService                               $loginGotoService
     * @param IdentitySessionStateService                  $identitySessionStateService
     * @param WebLoginService                              $loginService
     * @param LoginCsrfCookieService                       $loginCsrfCookieService
     * @param AuthenticationService                        $authenticationService
     * @param AuthenticationAccountLockoutViewModelBuilder $accountLocketViewModelBuilder
     * @param TwoFaFeatureToggle                           $twoFaFeatureToggle
     * @param SuccessLoginResultRoutingService             $successLoginResultRoutingService
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
        TwoFaFeatureToggle $twoFaFeatureToggle,
        SuccessLoginResultRoutingService $successLoginResultRoutingService

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
        $this->successLoginResultRoutingService = $successLoginResultRoutingService;
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
            $this->layout()->setVariable('showOrganisationLogo', true);
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
            $loginForm = new LoginForm();
            $viewVars = [
                'gotoUrl' => $goto,
                'usernameField' => $this->hashFormField($loginForm->getUsernameField()),
                'passwordField' => $this->hashFormField($loginForm->getPasswordField()),
                'form' => $loginForm,
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
        $loginForm->setData($this->filterPostArrayForForm());
        if (false === $loginForm->isValid()) {
            $loginForm->resetPassword();
            $viewVars = [
                'usernameField' => $this->hashFormField($loginForm->getUsernameField()),
                'passwordField' => $this->hashFormField($loginForm->getPasswordField()),
                'form' => $loginForm,
                'gotoUrl' => $request->getPost(self::PARAM_GOTO),
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

        // Redirect on success
        return $this->applyActionResult($this->successLoginResultRoutingService->route($result, $request));
    }
    
    private function setUpLoginCsrfCookie(ViewModel $vm)
    {
        $csrfToken = $this->loginCsrfCookieService->ensureCsrfCookie($this->request, $this->response);
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

    private function showErrorOnAuthFail(WebLoginResult $result, LoginForm $loginForm)
    {
        $resultCode = $result->getCode();
        if (
            $resultCode == AuthenticationResultCode::INVALID_CREDENTIALS ||
            $resultCode == AuthenticationResultCode::ERROR ||
            $resultCode == AuthenticationResultCode::UNRESOLVABLE_IDENTITY
        ) {
            $loginForm->resetPassword();
            $viewVars = [
                'usernameField' => $this->hashFormField($loginForm->getUsernameField()),
                'passwordField' => $this->hashFormField($loginForm->getPasswordField()),
                'form' => $loginForm,
                'gotoUrl' => $this->request->getPost(self::PARAM_GOTO),
                'authError' => true,
            ];

            return $this->buildLoginPageViewModel($viewVars);
        }

        return $this->accountLocketViewModelBuilder->createFromAuthenticationResponse($result);
    }

    private function filterPostArrayForForm()
    {
        $postArray = $this->request->getPost()->toArray();
        $filteredArray = [];

        foreach ($postArray as $key => $value) {
            if (substr($key, 0, 5) === LoginForm::USERNAME) {
                $filteredArray[LoginForm::USERNAME] = $value;
            }
            if (substr($key, 0, 5) === LoginForm::PASSWORD) {
                $filteredArray[LoginForm::PASSWORD] = $value;
            }
        }

        return $filteredArray;
    }

    private function hashFormField(ElementInterface $formField)
    {
        $hash = hash('crc32', ''.microtime(true));

        $formField->setName($formField->getName().$hash);
        $formField->setAttribute('id', $formField->getName());

        return $formField;
    }
}
