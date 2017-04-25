<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardValidation\Controller;

use CoreTest\Controller\AbstractLightWebControllerTest;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCard;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardValidation;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Controller\RegisteredCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Form\SecurityCardValidationForm;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\AlreadyLoggedInTodayWithLostForgottenCardCookieService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\RegisteredCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use DvsaCommon\Configuration\MotConfig;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;
use Zend\Http\Response;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Zend\Authentication\AuthenticationService;
use Zend\Di\ServiceLocator;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Parameters;
use Zend\View\Helper\Url;
use Zend\View\HelperPluginManager;
use Zend\View\Helper\HeadTitle;

class RegisteredCardControllerTest extends AbstractLightWebControllerTest
{
    const PIN = 123456;
    const ACTIVATION_DATE = '2016-10-05 15:39:42';
    const LOGIN_WITH_2FA_TEMPLATE = '2fa/registered-card/login-2fa';
    const INVALID_PIN_ERROR_MESSAGE = 'Enter a valid PIN number';
    const GTM_USER_LOGIN_FAILED = 'user-login-failed';
    const WRONG_PIN = 'wrong-pin';

    /** @var AuthenticationService $authenticationService */
    private $authenticationService;

    /** @var RegisteredCardService $registeredCardService */
    private $registeredCardService;

    /** @var TwoFaFeatureToggle $featureToggle */
    private $featureToggle;

    /** @var AlreadyLoggedInTodayWithLostForgottenCardCookieService $pinEntryCookieService */
    private $pinEntryCookieService;

    /** @var ServiceManager $serviceManager */
    protected $serviceManager;

    /** @var Request $request */
    private $request;

    /** @var Response $response */
    private $response;

    /** @var SecurityCardValidationForm $form */
    private $form;

    /** @var Identity */
    private $identity;

    public function setUp()
    {
        parent::setUp();
        $this->authenticationService = XMock::of(AuthenticationService::class);
        $this->registeredCardService = XMock::of(RegisteredCardService::class);
        $this->featureToggle = XMock::of(TwoFaFeatureToggle::class);
        $this->form = XMock::of(SecurityCardValidationForm::class);
        $this->request = new Request();
        $this->response = new Response();
        $this->pinEntryCookieService = XMock::of(AlreadyLoggedInTodayWithLostForgottenCardCookieService::class);
        $this->identity = XMock::of(Identity::class);
        $this->config = XMock::of(MotConfig::class);
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

    public function testOnGetLoginAction_whenAlreadyLoggedInTodayViaLostForgottenCard_shouldRedirectToLostForgotten()
    {
        $this
            ->withIs2FALoginApplicableToCurrentUser(true)
            ->withHasFeatureToggle(true)
            ->withAlreadyLoggedInTodayViaLostForgotten(true)
            ->withRegisteredSecurityCard()
            ->withActivationOccouringAfterCookie(true);

        $this->expectRedirect(LostOrForgottenCardController::START_ROUTE);
        $this->buildController()->login2FAAction();
    }

    public function testOnPostLoginAction_when2FALoginApplicableToUser_and_2FAFeatureToggleOn_and_valid2FAPIN_and_validForm_shouldRedirectToUserHome()
    {
        $data = new \stdClass();
        $data->pinValid = true;
        $data->lockedOut = false;

        $this->registeredCardService
            ->expects($this->once())
            ->method('getSecurityCardValidation')
            ->willReturn(new SecurityCardValidation($data));

        $this->registeredCardService
            ->expects($this->once())
            ->method('isLockedOut')
            ->willReturn(false);

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
        $data = new \stdClass();
        $data->pinValid = false;
        $data->lockedOut = false;
        $data->lockedOutFromNextFailure = false;

        $this->registeredCardService
            ->expects($this->once())
            ->method('getSecurityCardValidation')
            ->willReturn(new SecurityCardValidation($data));

        $this->registeredCardService
            ->expects($this->once())
            ->method('isLockedOut')
            ->willReturn(false);

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

        $gtmData = $vm->getVariable("gtmData");
        $this->assertEquals(self::GTM_USER_LOGIN_FAILED, $gtmData['event']);
        $this->assertEquals(self::WRONG_PIN, $gtmData['reason']);
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

    /**
     * @param boolean $alreadyUsedLostForgotten
     * @return $this
     */
    private function withAlreadyLoggedInTodayViaLostForgotten($alreadyUsedLostForgotten)
    {
        $this
            ->pinEntryCookieService
            ->expects($this->any())
            ->method('hasLoggedInTodayWithLostForgottenCardJourney')
            ->willReturn($alreadyUsedLostForgotten);

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

    /**
     * @param boolean $isFeatureToggleEnabled
     * @return $this
     */
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

    private function withActivationOccouringAfterCookie($hasActivationOccoured)
    {
        $this->pinEntryCookieService
            ->expects($this->once())
            ->method('hasLoggedInTodayWithLostForgottenCardJourney')
            ->willReturn($hasActivationOccoured);

        return $this;
    }

    private function withRegisteredSecurityCard()
    {
        $securityCard = new SecurityCard((object) ['activationDate' => self::ACTIVATION_DATE]);
        $this->registeredCardService
            ->expects($this->once())
            ->method('getLastRegisteredCard')
            ->willReturn($securityCard);

        return $this;
    }

    private function buildController()
    {
        $controller = new RegisteredCardController(
            $this->registeredCardService,
            $this->authenticationService,
            $this->request,
            $this->response,
            $this->featureToggle,
            $this->pinEntryCookieService,
            $this->identity,
            $this->config
        );

        $serviceLocator = new ServiceManager;
        $serviceLocator->setAllowOverride(true);
        $serviceLocator->setService('Feature\FeatureToggles', $this->featureToggle);

        $helperPluginManager = $this->getHelperPluginManager();
        $serviceLocator->setService('ViewHelperManager', $helperPluginManager);

        $controller->setServiceLocator($serviceLocator);

        $this->setController($controller);

        return $controller;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     * @throws \Exception
     */
    private function getHelperPluginManager()
    {
        $helperPluginManager = XMock::of(HelperPluginManager::class);
        $helperPluginManager
            ->expects($this->any())
            ->method('get')
            ->with('headTitle')
            ->willReturn(XMock::of(HeadTitle::class));
        return $helperPluginManager;
    }
}