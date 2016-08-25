<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Form\SecurityCardValidationForm;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\RegisteredCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Math\Rand;
use Zend\Mvc\MvcEvent;
use Zend\Session\ManagerInterface;
use Zend\View\Model\ViewModel;

class RegisteredCardController extends AbstractDvsaActionController
{
    const TWO_FACTOR_AUTH_LOGIN_PAGE_TITLE = 'Your security card PIN';
    const TWO_FACTOR_AUTH_LOGIN_PAGE_SUBTITLE = "Sign in";
    const ROUTE_FORGOTTEN_SECURITY_CARD = 'login';
    const ROUTE_SECURITY_CARD_LOST_DAMAGED = 'login';
    const ROUTE = 'login-2fa';

    /** @var RegisteredCardService */
    private $registeredCardService;

    /** @var AuthenticationService */
    private $authenticationService;

    /** @var  TwoFaFeatureToggle */
    private $twoFaFeatureToggle;

    public function __construct
    (
        RegisteredCardService $registeredCardService,
        AuthenticationService $authenticationService,
        Request $request,
        TwoFaFeatureToggle $twoFaFeatureToggle
    ) {
        $this->registeredCardService = $registeredCardService;
        $this->authenticationService = $authenticationService;
        $this->request = $request;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
    }

    /**
     * @return ViewModel
     */
    public function login2FAAction()
    {
        $request = $this->getRequest();
        $isFeatureToggleEnabled = $this->twoFaFeatureToggle->isEnabled();
        $is2FALoginApplicableToCurrentUser = $this->registeredCardService->is2FALoginApplicableToCurrentUser();

        if (!$is2FALoginApplicableToCurrentUser || !$isFeatureToggleEnabled) {
            return $this->redirect()->toRoute(UserHomeController::ROUTE);
        }

        if ($request->isPost()) {
            $response = $this->onPost2FALoginAction();
        } else {
            $response = $this->onGet2FALoginAction();
        }

        if ($response instanceof ViewModel) {
            $this->layout('layout/layout-govuk.phtml');
            $this->layout()->setVariables([
                'pageTitle' => self::TWO_FACTOR_AUTH_LOGIN_PAGE_TITLE,
                'pageSubTitle' => self::TWO_FACTOR_AUTH_LOGIN_PAGE_SUBTITLE,
                'hideUserNav' => true
            ]);
        }
        return $response;
    }

    /**
     * @return ViewModel
     */
    public function onGet2FALoginAction()
    {
        $form = new SecurityCardValidationForm();
        return $this->setUpViewData($form);
    }

    public function onPost2FALoginAction()
    {
        $request = $this->getRequest();
        $form = new SecurityCardValidationForm();
        $form->setData($request->getPost()->toArray());

        if (!$form->isValid()) {
            return $this->setUpViewData($form);
        }

        $isPinValid = $this->registeredCardService->validatePin($form->getPinField()->getValue());

        if ($isPinValid) {
            return $this->redirect()->toRoute(UserHomeController::ROUTE);
        } else {
            $form->setCustomError($form->getPinField(), 'Enter a valid PIN number');
            return $this->setUpViewData($form);
        }
    }

    private function setUpViewData(SecurityCardValidationForm $form)
    {
        $form->getPinField()->setValue(null);
        $serialNumber = $this->registeredCardService->getSerialNumber();

        $viewModelData = [
            'serialNumber' => $serialNumber,
            'form' => $form,
        ];

        return (new ViewModel($viewModelData))->setTemplate('2fa/registered-card/login-2fa');
    }
}