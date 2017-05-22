<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\LostOrForgottenCard\Controller;

use CoreTest\Controller\AbstractLightWebControllerTest;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\AlreadyLoggedInTodayWithLostForgottenCardCookieService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Form\LostOrForgottenSecurityQuestionForm;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\AlreadyOrderedCardCookieService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Specification\ContainsTwoSecurityQuestionDtoSpecification;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Parameters;
use PHPUnit_Framework_MockObject_MockObject;

class LostOrForgottenCardControllerTest extends AbstractLightWebControllerTest
{
    const USER_ID = 10;
    const QUESTION_TEXT = 'test text';
    const SECURITY_QUESTION_ID_ONE = '123';
    const SECURITY_QUESTION_ID_TWO = '321';

    /** @var Request | PHPUnit_Framework_MockObject_MockObject */
    private $request;

    /** @var Response | PHPUnit_Framework_MockObject_MockObject */
    private $response;

    /** @var Identity | PHPUnit_Framework_MockObject_MockObject */
    private $identity;

    /** @var TwoFaFeatureToggle | PHPUnit_Framework_MockObject_MockObject */
    private $twoFaFeatureToggle;

    /** @var AlreadyLoggedInTodayWithLostForgottenCardCookieService | PHPUnit_Framework_MockObject_MockObject  */
    private $pinEntryCookieService;

    /** @var SecurityCardService | PHPUnit_Framework_MockObject_MockObject  */
    protected $securityCardService;

    private $stepArray = [
        LostOrForgottenCardController::START_ROUTE => false,
        LostOrForgottenCardController::QUESTIONS_ROUTE => false,
        LostOrForgottenCardController::CONFIRMATION_ROUTE => false,
    ];

    private $alreadyOrderedStepArray = [
        LostOrForgottenCardController::START_ALREADY_ORDERED_ROUTE => false,
        LostOrForgottenCardController::QUESTIONS_ROUTE => false,
        LostOrForgottenCardController::CONFIRMATION_ROUTE => false,
    ];

    private $questionOneStepArray = [
        LostOrForgottenCardController::LOGIN_SESSION_ROUTE => true,
        LostOrForgottenCardController::QUESTIONS_ROUTE => false,
        LostOrForgottenCardController::CONFIRMATION_ROUTE => false,
    ];

    /**
     * @var LostOrForgottenService | PHPUnit_Framework_MockObject_MockObject
     */
    private $lostAndForgottenService;

    /**
     * @var AlreadyOrderedCardCookieService | PHPUnit_Framework_MockObject_MockObject
     */
    private $alreadyOrderedCardCookieService;

    public function setUp()
    {
        parent::setUp();

        $this->request = new Request();
        $this->response = new Response();
        $this->identity = XMock::of(Identity::class);
        $this->lostAndForgottenService = XMock::of(LostOrForgottenService::class);
        $this->securityCardService = XMock::of(SecurityCardService::class);
        $this->alreadyOrderedCardCookieService = XMock::of(AlreadyOrderedCardCookieService::class);
        $this->pinEntryCookieService = XMock::of(AlreadyLoggedInTodayWithLostForgottenCardCookieService::class);
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

    /**
     * Checks the conditions under which user should be directed to question one of the security questions.
     *
     * @dataProvider redirectionAlreadyOrderedDataProvider
     *
     * @param $hasLoggedInTodayViaLostForgottenCard
     * @param $isEnteringThroughAlreadyOrdered
     * @param $hasSeenOrderLandingPage
     * @param $redirectionToQuestionOne
     */
    public function testStartAction_whenAlreadyLoggedInTodayViaLostForgottenCard_redirectsToQuestionOne(
        $hasLoggedInTodayViaLostForgottenCard, $isEnteringThroughAlreadyOrdered, $hasSeenOrderLandingPage,
        $redirectionToQuestionOne)
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->isEnteringThroughAlreadyOrdered($isEnteringThroughAlreadyOrdered)
            ->withAlreadyOrderedCardCookie($hasSeenOrderLandingPage)
            ->withPinEntryCookie($hasLoggedInTodayViaLostForgottenCard);

        $controller = $this->buildController();

        if ($redirectionToQuestionOne) {
            $this->expectRedirect(LostOrForgottenCardController::QUESTIONS_ROUTE);
        } else {
            $this->expectNoRedirect();
        }

        $controller->startAlreadyOrderedAction();
    }

    /**
     * @return array
     */
    public function redirectionAlreadyOrderedDataProvider()
    {
        // hasLoggedInTodayViaLostForgottenCard, isEnteringThroughAlreadyOrdered, hasSeenOrderLandingPage,
        // redirectionToQuestionOne
        return [
            [true, true, true, true],
            [true, true, false, false],
            [true, false, true, true],
            [true, false, false, false],
            [false, true, true, false],
            [false, true, false, false],
            [false, false, true, true],
            [false, false, false, false],
        ];
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
        $this->withAlreadyOrderedCardCookie(true);

        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('saveSteps')
            ->with($this->questionOneStepArray);

        $controller = $this->buildController();

        $this->expectRedirect(LostOrForgottenCardController::QUESTIONS_ROUTE);
        $controller->startAlreadyOrderedAction();
    }

    public function testSecurityQuestionsActionWithNotAllowedOnStep()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTIONS_ROUTE, false);

        $controller = $this->buildController();

        $this->expectRedirect(LostOrForgottenCardController::START_ROUTE);
        $controller->securityQuestionsAction();
    }

    public function testSecurityQuestionsActionWithAllowedOnStepGetRequest()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTIONS_ROUTE, true)
            ->withQuestions();

        $this->request
            ->setMethod(Request::METHOD_GET);

        $controller = $this->buildController();

        $this->expectNoRedirect();
        $controller->securityQuestionsAction();
    }

    public function testSecurityQuestionsActionIfEnteringThroughAlreadyOrderedAndHasCookie_viewVariablesCorrect()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTIONS_ROUTE, true)
            ->withQuestions();

        $this->isEnteringThroughAlreadyOrdered(true);

        $this->request
            ->setMethod(Request::METHOD_GET);

        $controller = $this->buildController();

        $this->expectNoRedirect();
        $viewModel = $controller->securityQuestionsAction();
        $this->assertSame(
            LostOrForgottenCardController::START_ALREADY_ORDERED_ROUTE,
            $viewModel->getVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_GO_BACK_ROUTE));
        $this->assertSame(
            LostOrForgottenCardController::BACK_TEXT,
            $viewModel->getVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_GO_BACK_LABEL));
    }

    public function testSecurityQuestionsActionIfEnteringThroughQuestionOne_viewVariablesCorrect()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTIONS_ROUTE, true)
            ->withQuestions();

        $this->isEnteringThroughQuestionOne(true);

        $this->request
            ->setMethod(Request::METHOD_GET);

        $controller = $this->buildController();

        $this->expectNoRedirect();
        $viewModel = $controller->securityQuestionsAction();
        $this->assertSame(
            'logout',
            $viewModel->getVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_GO_BACK_ROUTE));
        $this->assertSame(
            LostOrForgottenCardController::RETURN_TO_SIGN_IN_TEXT,
            $viewModel->getVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_GO_BACK_LABEL));
    }

    public function testSecurityQuestionsActionIfNotEnteringThroughAlreadyOrdered_viewVariablesCorrect()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTIONS_ROUTE, true)
            ->withQuestions();

        $this->isEnteringThroughAlreadyOrdered(false);

        $this->request
            ->setMethod(Request::METHOD_GET);

        $controller = $this->buildController();

        $this->expectNoRedirect();
        $viewModel = $controller->securityQuestionsAction();
        $this->assertSame(
            LostOrForgottenCardController::START_ROUTE,
            $viewModel->getVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_GO_BACK_ROUTE));
        $this->assertSame(
            LostOrForgottenCardController::BACK_TEXT,
            $viewModel->getVariable(LostOrForgottenCardController::VIEW_MODEL_PARAM_GO_BACK_LABEL));
    }

    public function testConfirmationActionWithNotAllowedOnStep()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::CONFIRMATION_ROUTE, false);

        $controller = $this->buildController();

        $this->expectRedirect(LostOrForgottenCardController::QUESTIONS_ROUTE);
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

    public function testSecurityQuestionsActionPostSuccessRedirectToNextStep()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTIONS_ROUTE, true)
            ->withQuestions()
            ->withAnswersValid(true, true);

        $postData = $this->preparePostParams();

        $this->request
            ->setPost($postData)
            ->setMethod(Request::METHOD_POST);

        $controller = $this->buildController();

        $this->expectRedirect(LostOrForgottenCardController::CONFIRMATION_ROUTE);
        $controller->securityQuestionsAction();
    }

    public function testSecurityQuestionsActionPostAnswerNotCorrectStayOnStep()
    {
        $this
            ->withHasFeatureToggle(true)
            ->withTwoFactorRegisteredIdentity(true)
            ->withIsAllowedOnStep(LostOrForgottenCardController::QUESTIONS_ROUTE, true)
            ->withQuestions()
            ->withAnswersValid(false, false);

        $postData = $this->preparePostParams();

        $this->request
            ->setPost($postData)
            ->setMethod(Request::METHOD_POST);

        $controller = $this->buildController();

        $this->expectNoRedirect();
        $controller->securityQuestionsAction();
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
     * @param bool $isTwoFactorIdentity
     *
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
        $helpDeskConfig = [
            'phoneNumber' => '0330 123 5654',
            'openingHrsWeekdays' => 'Monday to Friday, 8am to 8pm',
            'openingHrsSaturday' => 'Saturday, 8am to 2pm',
            'openingHrsSunday' => 'Sunday, closed'
        ];

        $controller = new LostOrForgottenCardController(
            $this->request,
            $this->response,
            $this->identity,
            $this->twoFaFeatureToggle,
            $this->lostAndForgottenService,
            $this->securityCardService,
            $this->alreadyOrderedCardCookieService,
            $this->pinEntryCookieService,
            new ContainsTwoSecurityQuestionDtoSpecification(),
            $helpDeskConfig
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
     * @param bool   $isAllowedOnStep
     *
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
    private function withQuestions()
    {
        $securityQuestionDtoOne = new SecurityQuestionDto();
        $securityQuestionDtoOne
            ->setId(self::USER_ID)
            ->setText(self::QUESTION_TEXT);

        $securityQuestionDtoTwo = new SecurityQuestionDto();
        $securityQuestionDtoTwo
            ->setId(self::USER_ID)
            ->setText(self::QUESTION_TEXT);

        $apiResponse = [$securityQuestionDtoOne, $securityQuestionDtoTwo];

        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('getQuestionsForPerson')
            ->willReturn($apiResponse);

        return $this;
    }

    /**
     * @param bool $isAnswerOneValid
     * @param bool $isAnswerTwoValid
     *
     * @return $this
     */
    private function withAnswersValid($isAnswerOneValid, $isAnswerTwoValid)
    {
        $validationResponseResult = [
            self::SECURITY_QUESTION_ID_ONE => $isAnswerOneValid,
            self::SECURITY_QUESTION_ID_TWO => $isAnswerTwoValid,
        ];
        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('verifyAnswersForPerson')
            ->willReturn($validationResponseResult);

        return $this;
    }

    private function isEnteringThroughAlreadyOrdered($isEnteringThroughAlreadyOrdered)
    {
        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('isEnteringThroughAlreadyOrdered')
            ->willReturn($isEnteringThroughAlreadyOrdered);

        return $this;
    }

    private function isEnteringThroughQuestionOne($isEnteringThroughQuestionOne)
    {
        $this->lostAndForgottenService
            ->expects($this->once())
            ->method('isEnteringThroughSecurityQuestionOne')
            ->willReturn($isEnteringThroughQuestionOne);

        return $this;
    }

    private function withAlreadyOrderedCardCookie($hasCookie)
    {
        $this->alreadyOrderedCardCookieService
            ->expects($this->once())
            ->method('hasSeenOrderLandingPage')
            ->willReturn($hasCookie);

        return $this;
    }

    private function withPinEntryCookie($hasLoggedInTodayViaLostForgottenCard)
    {
        $this->pinEntryCookieService
            ->expects($this->once())
            ->method('hasLoggedInTodayWithLostForgottenCardJourney')
            ->with($this->request)
            ->willReturn($hasLoggedInTodayViaLostForgottenCard);

        return $this;
    }

    /**
     * @return Parameters
     */
    protected function preparePostParams()
    {
        $postData = new Parameters([
            LostOrForgottenSecurityQuestionForm::FORM_FIELD_NAME_ANSWER_ONE => 'valid submission',
            LostOrForgottenSecurityQuestionForm::FORM_FIELD_NAME_ANSWER_TWO => 'valid submission',
        ]);

        return $postData;
    }
}
