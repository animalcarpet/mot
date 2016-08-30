<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardValidation\Controller;

use CoreTest\Controller\AbstractLightWebControllerTest;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Controller\RegisteredCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Form\SecurityCardValidationForm;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\RegisteredCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Zend\Authentication\AuthenticationService;
use Zend\Di\ServiceLocator;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\ResponseInterface as Response;

class RegisteredCardControllerTest extends AbstractLightWebControllerTest
{
    const PIN = 123456;
    const LOGIN_WITH_2FA_TEMPLATE = '2fa/registered-card/login-2fa';
    const INVALID_PIN_ERROR_MESSAGE = 'Enter a valid PIN number';

    /** @var AuthenticationService $authenticationService */
    private $authenticationService;

    /** @var  RegisteredCardService $registeredCardService */
    private $registeredCardService;

    private $featureToggle;

    /**
     * @var ServiceManager $serviceManager
     */
    protected $serviceManager;

    private $request;

    private $form;

    public function setUp()
    {
        parent::setUp();

        $this->authenticationService = XMock::of(AuthenticationService::class);
        $this->registeredCardService = XMock::of(RegisteredCardService::class);
        $this->featureToggle         = XMock::of(TwoFaFeatureToggle::class);
        $this->form                  = XMock::of(SecurityCardValidationForm::class);
        $this->request               = new Request();
    }

    public function testOn2FALoginAction_when2FALoginNotApplicableToUser_and_2FAFeatureToggleOff_shouldRedirectToUserHome()
    {
        $this
            ->withIs2FALoginApplicableToCurrentUser(false)
            ->withHasFeatureToggle(false);

        $this->expectRedirect(UserHomeController::ROUTE);
        $this->buildController()->login2FAAction();
    }

    public function testOn2FALoginAction_when2FALoginNotApplocableToUser_and_2FAFeatureToggleOn_shouldRedirectToUserHome()
    {
        $this
            ->withIs2FALoginApplicableToCurrentUser(false)
            ->withHasFeatureToggle(true);

        $this->expectRedirect(UserHomeController::ROUTE);
        $this->buildController()->login2FAAction();
    }

    public function testOnGetLoginAction_when2FALoginApplicableToUser_and_2FAFeatureToggleOn_shouldRedirectToLoginWith2FA()
    {
        $expectedTemplate = self::LOGIN_WITH_2FA_TEMPLATE;

        $this
            ->withIs2FALoginApplicableToCurrentUser(true)
            ->withHasFeatureToggle(true);

        $vm = $this->buildController()->login2FAAction();
        $this->assertEquals($expectedTemplate, $vm->getTemplate());
        $this->assertEquals(200, $this->getController()->getResponse()->getStatusCode());
    }

    public function testOnPostLoginAction_when2FALoginApplicableToUser_and_2FAFeatureToggleOn_and_valid2FAPIN_and_validForm_shouldRedirectToUserHome()
    {
        $this
            ->withIs2FALoginApplicableToCurrentUser(true)
            ->withHasFeatureToggle(true)
            ->withValidFormSubmission()
            ->withSuccessfulPinValidation(true);

        $this->expectRedirect(UserHomeController::ROUTE);

        $controller = $this->buildController();
        $controller->login2FAAction();
    }

    public function testOnPostLoginAction_when2FALoginApplicableToUser_and_2FAFeatureToggleOn_and_inValidPIN_and_validForm_shouldShowLoginWith2FA()
    {
        $this
            ->withIs2FALoginApplicableToCurrentUser(true)
            ->withHasFeatureToggle(true)
            ->withValidFormSubmission()
            ->withSuccessfulPinValidation(false);

        $vm = $this->buildController()->login2FAAction();

        /** @var SecurityCardValidationForm $form */
        $form = $vm->getVariable('form');
        $this->assertPinInputIsCleared($form);
        $this->assertContains(self::INVALID_PIN_ERROR_MESSAGE, $form->getPinField()->getMessages());
        $this->assertEquals(self::LOGIN_WITH_2FA_TEMPLATE, $vm->getTemplate());
    }

    public function testOnPostLoginAction_when2FALoginApplicableToUser_and_2FAFeatureToggleOn_and_inValidForm_shouldShowLoginWith2FA()
    {
        $expectedTemplate = self::LOGIN_WITH_2FA_TEMPLATE;

        $this
            ->withIs2FALoginApplicableToCurrentUser(true)
            ->withHasFeatureToggle(true)
            ->withInvalidFormSubmission("0");

        $vm = $this->buildController()->login2FAAction();

        $this->assertEquals($expectedTemplate, $vm->getTemplate());
        $this->assertPinInputIsCleared($vm->getVariable('form'));
        $this->assertNotNull($vm->getVariable("gtmData"));
    }

    private function withIs2FALoginApplicableToCurrentUser($isApplicable)
    {
        $this
            ->registeredCardService
            ->expects($this->any())
            ->method('is2FALoginApplicableToCurrentUser')
            ->willReturn($isApplicable);

        return $this;
    }

    private function withSuccessfulPinValidation($isValidPin)
    {
        $this
            ->registeredCardService
            ->expects($this->any())
            ->method('validatePin')
            ->with(self::PIN)
            ->willReturn($isValidPin);

        return $this;
    }

    private function withPostSubmission()
    {
        $this->request->setMethod(Request::METHOD_POST);

        return $this;
    }

    private function withValidFormSubmission()
    {
        $this->withPostSubmission();

        $this->request->setPost(new Parameters([
            SecurityCardValidationForm::PIN => self::PIN
        ]));

        return $this;
    }

    private function withInvalidFormSubmission($pin = 0)
    {
        $this->withPostSubmission();

        $this->request->setPost(new Parameters([
            SecurityCardValidationForm::PIN => $pin
        ]));

        return $this;
    }

    private function withHasFeatureToggle($isFeatureToggleEnabled)
    {
        $this->featureToggle
            ->expects($this->any())
            ->method('isEnabled')
            ->willReturn($isFeatureToggleEnabled);

        return $this;
    }

    private function assertPinInputIsCleared(SecurityCardValidationForm $form)
    {
        $this->assertEmpty($form->getPinField()->getValue());
    }

    private function buildController()
    {
        $controller = new RegisteredCardController(
            $this->registeredCardService,
            $this->authenticationService,
            $this->request,
            $this->featureToggle
        );

        $serviceLocator = new ServiceManager;
        $serviceLocator->setAllowOverride(true);
        $serviceLocator->setService('Feature\FeatureToggles', $this->featureToggle);

        $controller->setServiceLocator($serviceLocator);

        $this->setController($controller);

        return $controller;
    }
}