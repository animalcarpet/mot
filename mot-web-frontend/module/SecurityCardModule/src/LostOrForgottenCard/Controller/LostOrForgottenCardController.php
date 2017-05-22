<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\AlreadyLoggedInTodayWithLostForgottenCardCookieService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\AlreadyOrderedCardCookieService;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Specification\ContainsTwoSecurityQuestionDtoSpecification;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\ViewModel\ConfirmationViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\ViewModel\SecurityQuestionsViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\ViewModel\StartAlreadyOrderedViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCard;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Form\LostOrForgottenSecurityQuestionForm;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use DvsaCommon\Utility\ArrayUtils;
use Zend\View\Model\ViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\ViewModel\StartLostAndForgottenViewModel;

class LostOrForgottenCardController extends AbstractDvsaActionController
{
    const LOGOUT_ROUTE = 'logout';
    const LOGIN_SESSION_ROUTE = 'login';
    const START_ROUTE = 'lost-or-forgotten-card';
    const START_ALREADY_ORDERED_ROUTE = 'lost-or-forgotten-card/already-ordered';
    const QUESTIONS_ROUTE = 'lost-or-forgotten-card/security-questions';
    const CONFIRMATION_ROUTE = 'lost-or-forgotten-card/confirmation';
    const FORGOTTEN_SECURITY_QUESTION_ROUTE = 'lost-or-forgotten-card/forgot-question';

    const QUESTION_ONE = 0;
    const QUESTION_TWO = 1;
    const MSG_INCORRECT_ANSWER = "Your answer wasn’t right.";
    const STEP_INVALID = false;
    const STEP_VALID = true;
    const BACK_TEXT = 'Back';
    const RETURN_TO_SIGN_IN_TEXT = 'Cancel and return to sign in';

    const TEMPLATE_2FA_START = '2fa/lost-forgotten/start';
    const TEMPLATE_2FA_START_ALREADY_ORDERED = '2fa/lost-forgotten/start-already-ordered';
    const TEMPLATE_2FA_SECURITY_QUESTION_ONE = '2fa/lost-forgotten/security-questions';
    const TEMPLATE_2FA_CONFIRMATION = '2fa/lost-forgotten/confirmation';

    const VIEW_MODEL_PARAM_FORM = 'form';
    const VIEW_MODEL_PARAM_SHOW_CARD_ORDER_LINK = 'showCardOrderLink';
    const VIEW_MODEL_PARAM_GO_BACK_ROUTE = 'backRoute';
    const VIEW_MODEL_PARAM_GO_BACK_LABEL = 'backText';

    const VIEW_QUESTIONS_PAGE_TITLE = 'Your security questions';
    const VIEW_QUESTIONS_PAGE_SUBTITLE = 'Sign in without your security card';
    const VIEW_CONFIRMATION_PAGE_TITLE = 'Sign in successful';
    const VIEW_ALREADY_ORDERED_PAGE_SUBTITLE = self::VIEW_QUESTIONS_PAGE_SUBTITLE;
    const VIEW_ALREADY_ORDERED_PAGE_TITLE = 'Sign in';

    const VIEW_MODEL_PARAM_HELPDESK_PHONE_NUMBER = 'phoneNumber';
    const VIEW_MODEL_PARAM_HELPDESK_OPENING_HOURS_WEEKDAYS = 'openingHrsWeekdays';
    const VIEW_MODEL_PARAM_HELPDESK_OPENING_HOURS_SATURDAY = 'openingHrsSaturday';
    const VIEW_MODEL_PARAM_HELPDESK_OPENING_HOURS_SUNDAY = 'openingHrsSunday';

    /** @var SecurityCardService $securityCardService */
    protected $securityCardService;

    /** @var Identity $identity */
    private $identity;

    /** @var TwoFaFeatureToggle $twoFaFeatureToggle */
    private $twoFaFeatureToggle;

    /** @var LostOrForgottenService $lostAndForgottenService */
    private $lostAndForgottenService;

    /** @var AlreadyOrderedCardCookieService $alreadyOrderedCardCookieService */
    private $alreadyOrderedCardCookieService;

    /** @var AlreadyLoggedInTodayWithLostForgottenCardCookieService $pinEntryCookieService */
    private $pinEntryCookieService;

    /**@var ContainsTwoSecurityQuestionDtoSpecification */
    private $containsTwoSecurityQuestionDtoSpec;

    /** @var array $helpDeskConfigInfo */
    private $helpDeskConfigInfo;

    public function __construct(
        Request $request,
        Response $response,
        Identity $identity,
        TwoFaFeatureToggle $twoFaFeatureToggle,
        LostOrForgottenService $lostAndForgottenService,
        SecurityCardService $securityCardService,
        AlreadyOrderedCardCookieService $alreadyOrderedCardCookieService,
        AlreadyLoggedInTodayWithLostForgottenCardCookieService $cookieService,
        ContainsTwoSecurityQuestionDtoSpecification $containsTwoSecurityQuestionDtoSpec,
        array $helpDeskConfigInfo
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->identity = $identity;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
        $this->lostAndForgottenService = $lostAndForgottenService;
        $this->securityCardService = $securityCardService;
        $this->alreadyOrderedCardCookieService = $alreadyOrderedCardCookieService;
        $this->pinEntryCookieService = $cookieService;
        $this->containsTwoSecurityQuestionDtoSpec = $containsTwoSecurityQuestionDtoSpec;
        $this->helpDeskConfigInfo = $helpDeskConfigInfo;
    }

    /**
     * @param MvcEvent $e
     *
     * @return mixed|\Zend\Http\Response
     */
    public function onDispatch(MvcEvent $e)
    {
        $featureToggleNotEnabled = !$this->twoFaFeatureToggle->isEnabled();
        $identityNotRegisteredFor2fa = !$this->identity->isSecondFactorRequired();

        if ($featureToggleNotEnabled || $identityNotRegisteredFor2fa) {
            return $this->redirect()->toRoute(UserHomeController::ROUTE);
        }

        return parent::onDispatch($e);
    }

    public function startAction()
    {
        $hasLoggedInTodayViaLostForgottenCard
            = $this->pinEntryCookieService->hasLoggedInTodayWithLostForgottenCardJourney($this->request);
        if ($hasLoggedInTodayViaLostForgottenCard) {
            $this->loadStepsIntoSessionQuestionOne();
            $this->lostAndForgottenService->updateStepStatus(self::LOGIN_SESSION_ROUTE, self::STEP_VALID);

            return $this->redirect()->toRoute(self::QUESTIONS_ROUTE);
        }

        $this->loadStepsIntoSession();
        $this->lostAndForgottenService->updateStepStatus(self::START_ROUTE, self::STEP_VALID);
        $showCardOrderLink = $this->identity->isSecondFactorRequired() && $this->currentIdentityHasActiveSecurityCard();

        $viewModel = new StartLostAndForgottenViewModel();
        $viewModel->setShowCardOrderLink($showCardOrderLink);

        return $viewModel;
    }

    public function startAlreadyOrderedAction()
    {
        $hasLoggedInTodayViaLostForgottenCard
            = $this->pinEntryCookieService->hasLoggedInTodayWithLostForgottenCardJourney($this->request);

        $isEnteringThroughAlreadyOrdered = $this->lostAndForgottenService->isEnteringThroughAlreadyOrdered();

        $hasSeenOrderLandingPage = $this->alreadyOrderedCardCookieService->hasSeenOrderLandingPage($this->getRequest());

        $preventOrderLandingPage = !$isEnteringThroughAlreadyOrdered && $hasSeenOrderLandingPage;

        if ($preventOrderLandingPage || ($hasSeenOrderLandingPage && $hasLoggedInTodayViaLostForgottenCard)) {
            $this->loadStepsIntoSessionQuestionOne();
            $this->lostAndForgottenService->updateStepStatus(self::LOGIN_SESSION_ROUTE, self::STEP_VALID);

            return $this->redirect()->toRoute(self::QUESTIONS_ROUTE);
        }

        $this->loadStepsIntoSessionAlreadyOrdered();
        $this->lostAndForgottenService->updateStepStatus(self::START_ALREADY_ORDERED_ROUTE, self::STEP_VALID);

        $this->alreadyOrderedCardCookieService->addAlreadyOrderedCardCookie($this->getResponse());
        $viewModel = new StartAlreadyOrderedViewModel();

        return $viewModel;
    }

    public function securityQuestionsAction()
    {
        if (!$this->lostAndForgottenService->isAllowedOnStep(self::QUESTIONS_ROUTE)) {
            return $this->redirect()->toRoute(self::START_ROUTE);
        }

        $personId = $this->identity->getUserId();
        $questions = $this->fetchQuestionsForPerson($personId);
        $form = $this->prepareForm($questions);
        $viewModel = $this->createViewModel();
        $viewModel->setForm($form);

        if (!$this->request->isPost()) {
            return $viewModel;
        }

        $form->setData($this->request->getPost()->toArray());

        if (!$form->isValid()) {
            $viewModel->setForm($form);

            return $viewModel;
        }

        $apiCallData = $this->prepareDataForApiCall($questions, $form);
        $apiValidationResponse = $this->lostAndForgottenService->verifyAnswersForPerson($personId, $apiCallData);

        if($this->areAnswersCorrect($apiValidationResponse)){
            $this->lostAndForgottenService->updateStepStatus(self::QUESTIONS_ROUTE, self::STEP_VALID);

            return $this->redirect()->toRoute(self::CONFIRMATION_ROUTE);
        }

        $this->handleErrorsFromApi($form, $apiValidationResponse);
        $viewModel->setForm($form);

        return $viewModel;
    }

    public function confirmationAction()
    {
        if (!$this->lostAndForgottenService->isAllowedOnStep(self::CONFIRMATION_ROUTE)) {
            return $this->redirect()->toRoute(self::QUESTIONS_ROUTE);
        }

        $this->pinEntryCookieService->addLoggedInViaLostForgottenCardCookie($this->response);

        $this->lostAndForgottenService->clearSession();
        $viewModel = new ConfirmationViewModel();

        $this->identity->setAuthenticatedWith2FA(true);
        $this->identity->setAuthenticatedWithLostForgotten(true);

        $showCardOrderLink = $this->identity->isSecondFactorRequired() && $this->currentIdentityHasActiveSecurityCard();
        $viewModel->setShowCardOrderLink( $showCardOrderLink);

        return $viewModel;
    }

    private function loadStepsIntoSession()
    {
        $stepArray = [
            self::START_ROUTE => self::STEP_INVALID,
            self::QUESTIONS_ROUTE => self::STEP_INVALID,
            self::CONFIRMATION_ROUTE => self::STEP_INVALID,
        ];

        $this->lostAndForgottenService->saveSteps($stepArray);
    }

    private function loadStepsIntoSessionAlreadyOrdered()
    {
        $stepArray = [
            self::START_ALREADY_ORDERED_ROUTE => self::STEP_INVALID,
            self::QUESTIONS_ROUTE => self::STEP_INVALID,
            self::CONFIRMATION_ROUTE => self::STEP_INVALID,
        ];

        $this->lostAndForgottenService->saveSteps($stepArray);
    }

    private function loadStepsIntoSessionQuestionOne()
    {
        $stepArray = [
            self::LOGIN_SESSION_ROUTE => self::STEP_VALID,
            self::QUESTIONS_ROUTE => self::STEP_INVALID,
            self::CONFIRMATION_ROUTE => self::STEP_INVALID,
        ];

        $this->lostAndForgottenService->saveSteps($stepArray);
    }

    protected function currentIdentityHasActiveSecurityCard()
    {
        $securityCard = $this->securityCardService->getSecurityCardForUser($this->identity->getUsername());

        return $securityCard instanceof SecurityCard && $securityCard->isActive();
    }

    /**
     * @param SecurityQuestionsViewModel $viewModel
     */
    protected function setUpGoBackLinkViewModelVariables(SecurityQuestionsViewModel $viewModel)
    {
        if ($this->lostAndForgottenService->isEnteringThroughAlreadyOrdered()) {
            $viewModel->setGoBackRoute(self::START_ALREADY_ORDERED_ROUTE);
            $viewModel->setGoBackLabel(self::BACK_TEXT);
        } elseif ($this->lostAndForgottenService->isEnteringThroughSecurityQuestionOne()) {
            $viewModel->setGoBackRoute(self::LOGOUT_ROUTE);
            $viewModel->setGoBackLabel(self::RETURN_TO_SIGN_IN_TEXT);
        } else {
            $viewModel->setGoBackRoute(self::START_ROUTE);
            $viewModel->setGoBackLabel(self::BACK_TEXT);
        }
    }

    /**
     * @param SecurityQuestionDto[] $questions
     * @return LostOrForgottenSecurityQuestionForm
     * @throws \Exception
     */
    protected function prepareForm(array $questions)
    {
        $questionOne = $questions[self::QUESTION_ONE];
        $questionTwo = $questions[self::QUESTION_TWO];

        $form = new LostOrForgottenSecurityQuestionForm(
            $questionOne->getText(),
            $questionTwo->getText()
        );

        return $form;
    }

    /**
     * @param int $personsId
     * @return \DvsaCommon\Dto\Security\SecurityQuestionDto[]
     * @throws \Exception
     */
    protected function fetchQuestionsForPerson($personsId)
    {
        $questions = $this->lostAndForgottenService->getQuestionsForPerson($personsId);

        if (!$this->isApiResponseValid($questions)) {
            throw new \Exception('Invalid response from API');
        }
        return $questions;
    }

    /**
     * @param null|array|SecurityQuestionDto[]  $questions
     * @return bool
     */
    protected function isApiResponseValid($questions)
    {
        return $this->containsTwoSecurityQuestionDtoSpec->isSatisfiedBy($questions);
    }

    /**
     * @param SecurityQuestionDto[] $questions
     * @param LostOrForgottenSecurityQuestionForm $form
     * @return array
     */
    private function prepareDataForApiCall(array $questions, LostOrForgottenSecurityQuestionForm $form)
    {
        $return = [];

        foreach([self::QUESTION_ONE, self::QUESTION_TWO] as $index) {
            /** @var SecurityQuestionDto $dto */
            $dto = $questions[$index];
            $questionId = $dto->getId();

            if(self::QUESTION_ONE === $index){
                $answer = $form->getAnswerOneField()->getValue();
            }
            else {
                $answer = $form->getAnswerTwoField()->getValue();
            }

            $return[$questionId] = $answer;
        }

        return $return;
    }

    /**
     * @param array $apiValidationResponse
     * @return bool
     */
    private function areAnswersCorrect(array $apiValidationResponse)
    {
        if(2 !== count($apiValidationResponse)) {
            return false;
        }

        list($validationResultQuestionOne, $validationResultQuestionTwo) = array_values($apiValidationResponse);

        return $validationResultQuestionOne && $validationResultQuestionTwo;
    }

    /**
     * @param LostOrForgottenSecurityQuestionForm $form
     * @param array $apiValidationResponse
     */
    private function handleErrorsFromApi(LostOrForgottenSecurityQuestionForm $form, array $apiValidationResponse)
    {
        $singleResponse = array_shift($apiValidationResponse);
        if(false === $singleResponse){
            $form->setCustomError($form->getAnswerOneField(), self::MSG_INCORRECT_ANSWER);
        }

        $singleResponse = array_shift($apiValidationResponse);
        if(false === $singleResponse){
            $form->setCustomError($form->getAnswerTwoField(), self::MSG_INCORRECT_ANSWER);
        }
    }

    /**
     * @return SecurityQuestionsViewModel
     */
    protected function createViewModel()
    {
        $viewModel = new SecurityQuestionsViewModel();
        $this->setUpGoBackLinkViewModelVariables($viewModel);
        $this->extractDvsaHelpDeskInfo($viewModel);

        return $viewModel;
    }

    protected function extractDvsaHelpDeskInfo(SecurityQuestionsViewModel $viewModel)
    {
        $phoneNumber = ArrayUtils::tryGet($this->helpDeskConfigInfo, 'phoneNumber', '0330 123 5654');
        $openingHrsWeekdays = ArrayUtils::tryGet($this->helpDeskConfigInfo, 'openingHrsWeekdays', 'Monday to Friday, 8am to 8pm');
        $openingHrsSaturday = ArrayUtils::tryGet($this->helpDeskConfigInfo, 'openingHrsSaturday', 'Saturday, 8am to 2pm');
        $openingHrsSunday = ArrayUtils::tryGet($this->helpDeskConfigInfo, 'openingHrsSunday', 'Sunday, closed');

        $viewModel->setDvsaPhoneNumber($phoneNumber);
        $viewModel->setDvsaOpeningHoursWeekdays($openingHrsWeekdays);
        $viewModel->setDvsaOpeningHoursSaturday($openingHrsSaturday);
        $viewModel->setDvsaOpeningHoursSunday($openingHrsSunday);
    }
}
