<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\LostOrForgottenCard\Controller;

use CoreTest\Controller\AbstractLightWebControllerTest;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\AlreadyOrderedCardCookieService;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Parameters;
use Zend\View\Model\ViewModel;

class LostOrForgottenCardControllerTest extends AbstractLightWebControllerTest
{
    const USER_ID = 10;
    const QUESTION_TEXT = 'test text';

    /** @var Request */
    private $request;

    /** @var Identity */
    private $identity;

    /** @var TwoFaFeatureToggle */
    private $twoFaFeatureToggle;

    /** @var SecurityCardService */
    protected $securityCardService;

    private $stepArray = [
        LostOrForgottenCardController::START_ROUTE => false,
        LostOrForgottenCardController::QUESTION_ONE_ROUTE => false,
        LostOrForgottenCardController::QUESTION_TWO_ROUTE => false,
        LostOrForgottenCardController::CONFIRMATION_ROUTE => false,
    ];

    private $alreadyOrderedStepArray = [
        LostOrForgottenCardController::START_ALREADY_ORDERED_ROUTE => false,
        LostOrForgottenCardController::QUESTION_ONE_ROUTE => false,
        LostOrForgottenCardController::QUESTION_TWO_ROUTE => false,
        LostOrForgottenCardController::CONFIRMATION_ROUTE => false,
    ];

    private $questionOneStepArray = [
        LostOrForgottenCardController::LOGIN_SESSION_ROUTE => true,
        LostOrForgottenCardController::QUESTION_ONE_ROUTE => false,
        LostOrForgottenCardController::QUESTION_TWO_ROUTE => false,
        LostOrForgottenCardController::CONFIRMATION_ROUTE => false,
    ];

    /**
     * @var LostOrForgottenService $lostAndForgottenService
     */
    private $lostAndForgottenService;

    /**
     * @var AlreadyOrderedCardCookieService $alreadyOrderedCardCookieService
     */
    private $alreadyOrderedCardCookieService;

    public function setUp()
    {
        parent::setUp();

        $this->request = new Request();
        $this->identity = XMock::of(Identity::class);
        $this->lostAndForgottenService = XMock::of(LostOrForgottenService::class);
        $this->securityCardService = XMock::of(SecurityCardService::class);
        $this->alreadyOrderedCardCookieService = XMock::of(AlreadyOrderedCardCookieService::class);
    }

    public function testOnDispatch_when2faFeatureToggleIsOff_shouldRedirectToHome()
    {
        $this
            ->withHasFeatureToggle(false)
            ->withTwoFactorRegisteredIdentity(true);

        $controller = $this->buildController();

        $this->expectRedirect(UserHomeController::ROUTE);

        $controller->onDispatch($this->getMvcEventForIndexAction());
    }

    public function testOnDispatch_when2faFeatureToggleIsOnButUserNot2fa_shouldRedirectToHome()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(false);

        $controller = $this->buildController();

        $this->expectRedirect(UserHomeController::ROUTE);

        $controller->onDispatch($this->getMvcEventForIndexAction());
    }

    public function testOnDispatch_when2faFeatureToggleIsOnAndUserIs2fa_shouldNotRedirect()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true);

        $controller = $this->buildController();

        $this->expectNoRedirect();

        $controller->onDispatch($this->getMvcEventForIndexAction());
    }

    public function testOnDispatchWhenAlreadyAuthenticatedShouldRedirectToHomePage()
    {
        $this
            ->withHasFeatureToggle(false)
            ->withTwoFactorRegisteredIdentity(false);

        $this->identity
            ->expects($this->once())
            ->method('isAuthenticatedWith2Fa')
            ->willReturn(true);

        $controller = $this->buildController();

        $this->expectRedirect(UserHomeController::ROUTE);

        $controller->onDispatch($this->getMvcEventForIndexAction());
    }

    public function testStartActionLoadsStepsIntoSession()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true);

        $controller = $this->buildController();

        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('saveSteps')
            ->with($this->stepArray);

        $this->expectNoRedirect();
        $controller->startAction();
    }

    public function testStartAlreadyOrderedActionLoadsStepsIntoSession()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true);

        $controller = $this->buildController();

        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('saveSteps')
            ->with($this->alreadyOrderedStepArray);

        $this->expectNoRedirect();
        $controller->startAlreadyOrderedAction();
    }

    public function testStartAlreadyOrderedActionRedirectsToQuestionOneWhenUserHasOrderedCardAndSeenPage()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true);

        $this->isEnteringThroughAlreadyOrdered(false);
        $this->withCookie(true);

        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('saveSteps')
            ->with($this->questionOneStepArray);

        $controller = $this->buildController();

        $this->expectRedirect(LostOrForgottenCardController::QUESTION_ONE_ROUTE);
        $controller->startAlreadyOrderedAction();
    }

    public function testQuestionOneActionWithNotAllowedOnStep()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTION_ONE_ROUTE, false);

        $controller = $this->buildController();

        $this->expectRedirect(LostOrForgottenCardController::START_ROUTE);
        $controller->securityQuestionOneAction();
    }

    public function testQuestionOneActionWithAllowedOnStepGetRequest()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTION_ONE_ROUTE, true)
            ->withQuestion();

        $this->request
            ->setMethod(Request::METHOD_GET);

        $controller = $this->buildController();

        $this->expectNoRedirect();
        $controller->securityQuestionOneAction();
    }

    public function testQuestionOneActionIfEnteringThroughAlreadyOrderedAndHasCookie_viewVariablesCorrect()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTION_ONE_ROUTE, true)
            ->withQuestion();

        $this->isEnteringThroughAlreadyOrdered(true);

        $this->request
            ->setMethod(Request::METHOD_GET);

        $controller = $this->buildController();

        $this->expectNoRedirect();
        $viewModel = $controller->securityQuestionOneAction();
        $this->assertSame(LostOrForgottenCardController::START_ALREADY_ORDERED_ROUTE, $viewModel->getVariable('backRoute'));
        $this->assertSame(LostOrForgottenCardController::BACK_TEXT, $viewModel->getVariable('backText'));
    }

    public function testQuestionOneActionIfEnteringThroughQuestionOne_viewVariablesCorrect()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTION_ONE_ROUTE, true)
            ->withQuestion();

        $this->isEnteringThroughQuestionOne(true);

        $this->request
            ->setMethod(Request::METHOD_GET);

        $controller = $this->buildController();

        $this->expectNoRedirect();
        $viewModel = $controller->securityQuestionOneAction();
        $this->assertSame('logout', $viewModel->getVariable('backRoute'));
        $this->assertSame(LostOrForgottenCardController::RETURN_TO_SIGN_IN_TEXT, $viewModel->getVariable('backText'));
    }

    public function testQuestionOneActionIfNotEnteringThroughAlreadyOrdered_viewVariablesCorrect()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTION_ONE_ROUTE, true)
            ->withQuestion();

        $this->isEnteringThroughAlreadyOrdered(false);

        $this->request
            ->setMethod(Request::METHOD_GET);

        $controller = $this->buildController();

        $this->expectNoRedirect();
        $viewModel = $controller->securityQuestionOneAction();
        $this->assertSame(LostOrForgottenCardController::START_ROUTE, $viewModel->getVariable('backRoute'));
        $this->assertSame(LostOrForgottenCardController::BACK_TEXT, $viewModel->getVariable('backText'));
    }

    public function testQuestionTwoActionWithNotAllowedOnStep()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTION_TWO_ROUTE, false);

        $controller = $this->buildController();

        $this->expectRedirect(LostOrForgottenCardController::QUESTION_ONE_ROUTE);
        $controller->securityQuestionTwoAction();
    }

    public function testQuestionTwoActionWithAllowedOnStepGetRequest()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTION_TWO_ROUTE, true)
            ->withQuestion();

        $controller = $this->buildController();

        $this->expectNoRedirect();
        $controller->securityQuestionTwoAction();
    }

    public function testConfirmationActionWithNotAllowedOnStep()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::CONFIRMATION_ROUTE, false);

        $controller = $this->buildController();

        $this->expectRedirect(LostOrForgottenCardController::QUESTION_TWO_ROUTE);
        $controller->confirmationAction();
    }

    public function testConfirmationActionWithAllowedOnStepGetRequest()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::CONFIRMATION_ROUTE, true);

        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('clearSession');

        $this->identity
            ->expects($this->once())
            ->method('setAuthenticatedWith2FA')
            ->with(true);


        $controller = $this->buildController();

        $this->expectNoRedirect();
        $controller->confirmationAction();
    }

    public function testSecurityQuestionOneActionPostSuccessRedirectToNextStep()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTION_ONE_ROUTE, true)
            ->withQuestion()
            ->withAnswerValid(true);

        $postData = new Parameters([
            'answer' => 'valid submission',
        ]);

        $this->request
            ->setPost($postData)
            ->setMethod(Request::METHOD_POST);

        $controller = $this->buildController();

        $this->expectRedirect(LostOrForgottenCardController::QUESTION_TWO_ROUTE);
        $controller->securityQuestionOneAction();
    }

    public function testSecurityQuestionOneActionPostAnswerNotCorrectStayOnStep()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTION_ONE_ROUTE, true)
            ->withQuestion()
            ->withAnswerValid(false);

        $postData = new Parameters([
            'answer' => 'valid submission',
        ]);

        $this->request
            ->setPost($postData)
            ->setMethod(Request::METHOD_POST);

        $controller = $this->buildController();

        $this->expectNoRedirect();
        $controller->securityQuestionOneAction();
    }

    public function testSecurityQuestionTwoActionPostSuccessRedirectToNextStep()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTION_TWO_ROUTE, true)
            ->withQuestion()
            ->withAnswerValid(true);

        $postData = new Parameters([
            'answer' => 'valid submission',
        ]);

        $this->request
            ->setPost($postData)
            ->setMethod(Request::METHOD_POST);

        $controller = $this->buildController();

        $this->expectRedirect(LostOrForgottenCardController::CONFIRMATION_ROUTE);
        $controller->securityQuestionTwoAction();
    }

    public function testSecurityQuestionTwoActionPostAnswerNotCorrectStayOnStep()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTION_TWO_ROUTE, true)
            ->withQuestion()
            ->withAnswerValid(false);

        $postData = new Parameters([
            'answer' => 'valid submission',
        ]);

        $this->request
            ->setPost($postData)
            ->setMethod(Request::METHOD_POST);

        $controller = $this->buildController();

        $this->expectNoRedirect();
        $controller->securityQuestionTwoAction();
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

    /**
     * @param bool $isFeatureToggleEnabled
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
     * @param bool $isTwoFactorIdentity
     * @return $this
     */
    private function withTwoFactorRegisteredIdentity($isTwoFactorIdentity)
    {
        $this->identity
            ->expects($this->any())
            ->method('isSecondFactorRequired')
            ->willReturn($isTwoFactorIdentity);

        return $this;
    }

    /**
     * @return LostOrForgottenCardController
     */
    private function buildController()
    {
        $controller = new LostOrForgottenCardController(
            $this->request,
            $this->identity,
            $this->twoFaFeatureToggle,
            $this->lostAndForgottenService,
            $this->securityCardService,
            $this->alreadyOrderedCardCookieService
        );

        $serviceLocator = new ServiceManager();
        $serviceLocator->setAllowOverride(true);
        $serviceLocator
            ->setService('Feature\FeatureToggles', XMock::of(FeatureToggles::class));

        $controller->setServiceLocator($serviceLocator);

        $this->setController($controller);
        $this->setUpPluginMocks();

        $layout = $controller->layout();
        $layout
            ->expects($this->any())
            ->method('setVariable')
            ->willReturn($layout);

        return $controller;
    }


    /**
     * @param string $currentStepRoute
     * @param bool $isAllowedOnStep
     * @return $this
     */
    private function withIsAllowedOnStep($currentStepRoute, $isAllowedOnStep)
    {
        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('isAllowedOnStep')
            ->with($currentStepRoute)
            ->willReturn($isAllowedOnStep);

        return $this;
    }

    /**
     * @return $this
     */
    private function withQuestion()
    {
        $securityQuestionDto = new SecurityQuestionDto();
        $securityQuestionDto
            ->setId(self::USER_ID)
            ->setText(self::QUESTION_TEXT);

        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('getQuestionForUser')
            ->willReturn($securityQuestionDto);

        return $this;
    }

    /**
     * @param bool $isAnswerValid
     * @return $this
     */
    private function withAnswerValid($isAnswerValid)
    {
        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('getAnswerForQuestion')
            ->willReturn($isAnswerValid);

        return $this;
    }

    private function isEnteringThroughAlreadyOrdered($isEnteringThroughAlreadyOrdered)
    {
        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('isEnteringThroughAlreadyOrdered')
            ->willReturn($isEnteringThroughAlreadyOrdered);
    }

    private function isEnteringThroughQuestionOne($isEnteringThroughQuestionOne)
    {
        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('isEnteringThroughSecurityQuestionOne')
            ->willReturn($isEnteringThroughQuestionOne);
    }

    private function withCookie($hasCookie)
    {
        $this->alreadyOrderedCardCookieService
            ->expects($this->once())
            ->method('hasSeenOrderLandingPage')
            ->willReturn($hasCookie);
    }
}

