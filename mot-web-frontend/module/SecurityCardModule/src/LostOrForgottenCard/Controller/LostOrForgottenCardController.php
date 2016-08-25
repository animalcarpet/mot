<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCard;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Form\LostOrForgottenSecurityQuestionForm;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class LostOrForgottenCardController extends AbstractDvsaActionController
{
    const START_ROUTE = 'lost-or-forgotten-card';
    const QUESTION_ONE_ROUTE = 'lost-or-forgotten-card/question-one';
    const QUESTION_TWO_ROUTE = 'lost-or-forgotten-card/question-two';
    const CONFIRMATION_ROUTE = 'lost-or-forgotten-card/confirmation';

    const QUESTION_ONE = 0;
    const QUESTION_TWO = 1;

    const MSG_INCORRECT_ANSWER = 'This is not the correct answer';

    const STEP_INVALID = false;
    const STEP_VALID = true;

    /**
     * @var SecurityCardService
     */
    protected $securityCardService;

    /**
     * @var array
     */
    private $questionOneSuccessMessage = ['First security question - your answer was ', 'correct.'];

    /**
     * @var Identity
     */
    private $identity;

    /**
     * @var TwoFaFeatureToggle
     */
    private $twoFaFeatureToggle;

    /**
     * @var LostOrForgottenService $lostAndForgottenService
     */
    private $lostAndForgottenService;

    public function __construct(
        Request $request,
        Identity $identity,
        TwoFaFeatureToggle $twoFaFeatureToggle,
        LostOrForgottenService $lostAndForgottenService,
        SecurityCardService $securityCardService
    ) {
        $this->request = $request;
        $this->identity = $identity;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
        $this->lostAndForgottenService = $lostAndForgottenService;
        $this->securityCardService = $securityCardService;
    }

    /**
     * @param MvcEvent $e
     * @return mixed|\Zend\Http\Response
     */
    public function onDispatch(MvcEvent $e)
    {
        $featureToggleNotEnabled = !$this->twoFaFeatureToggle->isEnabled();
        $identityNotRegisteredFor2fa = !$this->identity->isSecondFactorRequired();
        $alreadyAuthenticated = $this->identity->isAuthenticatedWith2FA();

        if ($featureToggleNotEnabled || $identityNotRegisteredFor2fa || $alreadyAuthenticated) {
            return $this->redirect()->toRoute(UserHomeController::ROUTE);
        }

        return parent::onDispatch($e);
    }

    public function startAction()
    {
        $this->loadStepsIntoSession();
        $this->lostAndForgottenService->updateStepStatus(self::START_ROUTE, self::STEP_VALID);
        $showCardOrderLink = $this->identity->isSecondFactorRequired() && $this->currentIdentityHasActiveSecurityCard();
        $viewModel = new ViewModel([]);
        $viewModel->setVariable('showCardOrderLink', $showCardOrderLink);
        $viewModel->setTemplate('2fa/lost-forgotten/start');

        return $viewModel;
    }

    public function securityQuestionOneAction()
    {
        if(!$this->lostAndForgottenService->isAllowedOnStep(self::QUESTION_ONE_ROUTE)) {
            return $this->redirect()->toRoute(self::START_ROUTE);
        }

        $question = $this->lostAndForgottenService->getQuestionForUser(self::QUESTION_ONE, $this->identity->getUserId());
        $form = new LostOrForgottenSecurityQuestionForm($question->getText());
        $viewModel = new ViewModel([]);
        $viewModel->setTemplate('2fa/lost-forgotten/security-question-one');

        if ($this->request->isPost()) {
            $form->setData($this->request->getPost()->toArray());

            if (!$form->isValid()) {
                $viewModel->setVariable('form', $form);
                return $viewModel;
            }

            if ($this->lostAndForgottenService->getAnswerForQuestion(
                $question->getId(),
                $this->identity->getUserId(),
                $form->getAnswerField()->getValue())
            ) {
                $this->flashMessenger()->addSuccessMessage($this->questionOneSuccessMessage);
                $this->lostAndForgottenService->updateStepStatus(self::QUESTION_ONE_ROUTE, self::STEP_VALID);
                $this->redirect()->toRoute(self::QUESTION_TWO_ROUTE);
            } else {
                $form->setCustomError($form->getAnswerField(), self::MSG_INCORRECT_ANSWER);
            }
        }
        $viewModel->setVariable('form', $form);
        return $viewModel;
    }

    public function securityQuestionTwoAction()
    {
        if(!$this->lostAndForgottenService->isAllowedOnStep(self::QUESTION_TWO_ROUTE)) {
            return $this->redirect()->toRoute(self::QUESTION_ONE_ROUTE);
        }

        $question = $this->lostAndForgottenService->getQuestionForUser(self::QUESTION_TWO, $this->identity->getUserId());
        $form = new LostOrForgottenSecurityQuestionForm($question->getText());
        $viewModel = new ViewModel([]);
        $viewModel->setTemplate('2fa/lost-forgotten/security-question-two');

        if ($this->request->isPost()) {
            $form->setData($this->request->getPost()->toArray());

            if (!$form->isValid()) {
                $viewModel->setVariable('form', $form);
                return $viewModel;
            }

            if ($this->lostAndForgottenService->getAnswerForQuestion(
                $question->getId(),
                $this->identity->getUserId(),
                $form->getAnswerField()->getValue())
            ) {
                $this->lostAndForgottenService->updateStepStatus(self::QUESTION_TWO_ROUTE, self::STEP_VALID);
                $this->redirect()->toRoute(self::CONFIRMATION_ROUTE);
            } else {
                $form->setCustomError($form->getAnswerField(), self::MSG_INCORRECT_ANSWER);
            }
        }
        $viewModel->setVariable('form', $form);
        return $viewModel;
    }

    public function confirmationAction()
    {
        if(!$this->lostAndForgottenService->isAllowedOnStep(self::CONFIRMATION_ROUTE)) {
            return $this->redirect()->toRoute(self::QUESTION_TWO_ROUTE);
        }

        $this->lostAndForgottenService->clearSession();
        $viewModel = new ViewModel([]);
        $this->identity->setAuthenticatedWith2FA(true);
        $this->identity->setAuthenticatedWithLostForgotten(true);

        $showCardOrderLink = $this->identity->isSecondFactorRequired() && $this->currentIdentityHasActiveSecurityCard();
        $viewModel->setVariable('showCardOrderLink', $showCardOrderLink);
        return $viewModel->setTemplate('2fa/lost-forgotten/confirmation');
    }

    private function loadStepsIntoSession() {
        $stepArray = [
            self::START_ROUTE => self::STEP_INVALID,
            self::QUESTION_ONE_ROUTE => self::STEP_INVALID,
            self::QUESTION_TWO_ROUTE => self::STEP_INVALID,
            self::CONFIRMATION_ROUTE => self::STEP_INVALID,
        ];

        $this->lostAndForgottenService->saveSteps($stepArray);
    }

    protected function currentIdentityHasActiveSecurityCard()
    {
        $securityCard = $this->securityCardService->getSecurityCardForUser($this->identity->getUsername());

        return $securityCard instanceof SecurityCard && $securityCard->isActive();
    }
}
