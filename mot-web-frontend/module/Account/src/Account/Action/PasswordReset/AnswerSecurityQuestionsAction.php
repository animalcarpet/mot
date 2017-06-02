<?php

namespace Account\Action\PasswordReset;

use Account\Controller\PasswordResetController;
use Account\Controller\SecurityQuestionController;
use Account\Form\SecurityQuestionAnswersForm;
use Account\Service\SecurityQuestionService;
use Account\ViewModel\AnswerSecurityQuestionsViewModel;
use Core\Action\AbstractActionResult;
use Core\Action\FlashNamespace;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\InputFilter\Account\SecurityQuestionAnswersInputFilter;
use RuntimeException;
use Zend\Http\Request;
use Zend\Stdlib\Parameters;

class AnswerSecurityQuestionsAction
{
    /** @var SecurityQuestionService $securityQuestionService */
    private $securityQuestionService;

    /** @var array $helpdeskConfig */
    private $helpdeskConfig;

    /** @var array $verificationMessages */
    private $verificationMessages = [];

    /** @var string $formActionUrl */
    private $formActionUrl;

    /** @var string $backUrl */
    private $backUrl;

    public function __construct(
        SecurityQuestionService $securityQuestionService,
        array $helpdeskConfig
    ) {
        $this->securityQuestionService = $securityQuestionService;
        $this->helpdeskConfig = $helpdeskConfig;
    }

    /**
     * @param $personId
     * @param Parameters $answerSubmission
     *
     * @return RedirectToRoute|ViewActionResult
     */
    public function execute($personId, Parameters $answerSubmission)
    {
        $form = $this->getSecurityQuestionAnswersFormForPerson($personId);

        $result = $this->validateAnswersThenSendEmailIfCorrect($answerSubmission, $form, $personId);
        if ($this->isRedirectResult($result)) {
            return $result;
        }

        $messages = $form->getMessages() + $this->verificationMessages;

        return $this->buildResult($result, $form, $messages);
    }

    /**
     * @param int $personId
     * @return ViewActionResult
     */
    public function executeNoAnswers($personId)
    {
        $form = $this->getSecurityQuestionAnswersFormForPerson($personId);

        return $this->buildResult(new ViewActionResult(), $form);
    }

    /**
     * @param ViewActionResult $result
     * @param SecurityQuestionAnswersForm $form
     * @param array $messages
     * @return ViewActionResult
     */
    private function buildResult(ViewActionResult $result, SecurityQuestionAnswersForm $form, array $messages = [])
    {
        $viewModel = new AnswerSecurityQuestionsViewModel();

        $viewModel->setForm($form);
        $viewModel->setValidationMessages($messages);
        $viewModel->setUrlBack($this->getBackUrl());
        $viewModel->setHelpdeskConfig($this->helpdeskConfig);

        $result->setViewModel($viewModel);
        $result->setTemplate('account/security-question/get-questions.twig');

        return $result;
    }

    /**
     * @param Parameters $answerSubmission
     * @param SecurityQuestionAnswersForm $form
     * @param $personId
     *
     * @return RedirectToRoute|ViewActionResult
     */
    private function validateAnswersThenSendEmailIfCorrect(
        Parameters $answerSubmission,
        SecurityQuestionAnswersForm $form,
        $personId
    ) {
        $form->bind($answerSubmission);

        if ($form->isValid()) {
            try {
                $verificationResult = $this->securityQuestionService
                    ->verifyAnswers($personId, $form->getMappedQuestionsAndAnswers());

                if ($this->securityQuestionService->isVerified()) {
                    return $this->sendEmailAndRedirect($personId);
                } else {
                    $this->collectVerificationMessages($verificationResult, $form);

                    if (!$this->securityQuestionService->hasRemainingAttempts()) {
                        return new RedirectToRoute(SecurityQuestionController::ROUTE_NOT_AUTHENTICATED);
                    }
                }
            } catch (RuntimeException $e) {
                return new RedirectToRoute(SecurityQuestionController::ROUTE_NOT_AUTHENTICATED);
            }
        }

        return new ViewActionResult();
    }

    /**
     * @param array $verificationResult
     * @param SecurityQuestionAnswersForm $form
     */
    private function collectVerificationMessages(array $verificationResult, SecurityQuestionAnswersForm $form)
    {
        $this->verificationMessages = [];

        foreach ($verificationResult as $failedQuestionId => $value) {
            $form->flagFailedAnswerVerifications($failedQuestionId);
        }

        if ($this->securityQuestionService->hasRemainingAttempts()) {
            if ($this->securityQuestionService->getRemainingAttempts() <= 1) {
                $this->verificationMessages = [[
                    SecurityQuestionAnswersInputFilter::MSG_LAST_ATTEMPT_WARNING,
                ]];
            }
        }
    }

    /**
     * @param $personId
     *
     * @return RedirectToRoute
     */
    private function sendEmailAndRedirect($personId)
    {
        $emailAddress = $this->securityQuestionService->resetPersonPassword($personId);

        $redirect = new RedirectToRoute(SecurityQuestionController::ROUTE_CONFIRMATION_EMAIL);
        $redirect->addFlashMessage(
            new FlashNamespace(PasswordResetController::SESSION_KEY_EMAIL),
            $emailAddress
        );

        return $redirect;
    }

    /**
     * @param $personId
     *
     * @return SecurityQuestionAnswersForm
     */
    private function getSecurityQuestionAnswersFormForPerson($personId)
    {
        $securityQuestions = $this->securityQuestionService->getQuestionsForPerson($personId);

        $form = new SecurityQuestionAnswersForm($securityQuestions[0], $securityQuestions[1]);

        $form->setAction($this->getFormActionUrl());

        $form->setInputFilter(new SecurityQuestionAnswersInputFilter());

        return $form;
    }

    /**
     * @param AbstractActionResult $result
     *
     * @return bool
     */
    private function isRedirectResult(AbstractActionResult $result)
    {
        return $result instanceof RedirectToRoute;
    }

    /**
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->formActionUrl === null) {
            throw new RuntimeException('Property $formActionUrl has not been set');
        }

        return $this->formActionUrl;
    }

    /**
     * @param string $formActionUrl
     * @return AnswerSecurityQuestionsAction
     */
    public function setFormActionUrl($formActionUrl)
    {
        $this->formActionUrl = $formActionUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->backUrl === null) {
            throw new RuntimeException('Property $backUrl has not been set');
        }

        return $this->backUrl;
    }

    /**
     * @param string $backUrl
     * @return AnswerSecurityQuestionsAction
     */
    public function setBackUrl($backUrl)
    {
        $this->backUrl = $backUrl;
        return $this;
    }
}
