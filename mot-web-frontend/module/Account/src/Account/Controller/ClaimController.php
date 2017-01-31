<?php

namespace Account\Controller;

use Account\Service\ClaimAccountService;
use Account\Validator\ClaimValidator;
use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Account\ViewModel\ReviewViewModel;

/**
 * Class ClaimController
 * @package Account\Controller
 */
class ClaimController extends AbstractAuthActionController
{
    const SERVICE_NAME = 'MOT testing service';

    const STEP_1_NAME = 'confirmPassword';
    const STEP_2_NAME = 'setSecurityQuestion';
    const STEP_3_NAME = 'review';
    const STEP_4_NAME = 'success';

    const PIN_ARRAY_KEY = 'pin';

    // Field names to display in Error Message Page Header for Reset Account Security process
    const NEW_PASSWORD_ERROR_MESSAGE_FIELD_NAME = 'New password - ';
    const RETYPE_YOUR_NEW_PASSWORD_ERROR_MESSAGE__FIELD_NAME = 'Re-type your new password - ';
    const FIRST_MEMORABLE_ANSWER_ERROR_MESSAGE_FIELD_NAME = 'Your first memorable answer - ';
    const SECOND_MEMORABLE_ANSWER_ERROR_MESSAGE_FIELD_NAME = 'Your second memorable answer - ';

    // Fields for Reset Account Security process
    const PASSWORD_FIELD = 'password';
    const CONFIRM_PASSWORD_FIELD = 'confirm_password';
    const FIRST_MEMORABLE_ANSWER_FIELD = 'answer_a';
    const SECOND_MEMORABLE_ANSWER_FIELD = 'answer_b';

    /** @var ClaimAccountService $claimAccountService */
    private $claimAccountService;

    /** @var ClaimValidator $claimValidator */
    private $claimValidator;

    /** @var MotIdentityProviderInterface $motIdentityProvider */
    private $motIdentityProvider;

    public function __construct(
        ClaimAccountService $claimAccountService,
        ClaimValidator $claimValidator,
        MotIdentityProviderInterface $motIdentityProvider
    ) {
        $this->claimAccountService = $claimAccountService;
        $this->claimValidator = $claimValidator;
        $this->motIdentityProvider = $motIdentityProvider;
    }
    
    public function resetAction()
    {
        $this->claimAccountService->clearSession();
        $this->redirectToStep(self::STEP_1_NAME);
    }

    /**
     * @return ViewModel
     */
    public function confirmPasswordAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = $request->getPost();

            $this->claimAccountService->captureStep($data);

            if ($this->claimValidator->validateStep(self::STEP_1_NAME, $data)) {
                $this->redirectToStep(self::STEP_2_NAME);
            }
        }

        $stepData = $this->getStepData(self::STEP_1_NAME);
        
        $stepData['messages'] = $this->claimValidator->getMessages();
        $stepData['summaryMessages'] = $this->getSummaryMessages();
        if ($this->flashMessenger()->hasErrorMessages()) {
            $stepData['messages'] = array_merge($stepData['messages'],
                [array_map('nl2br', $this->flashMessenger()->getErrorMessages())]);
        }

        $this->layout('layout/layout-govuk.phtml');

        return new ViewModel($stepData);
    }

    /**
     * @return Response|ViewModel
     */
    public function setSecurityQuestionAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = $request->getPost();

            $this->claimAccountService->captureStep($data);
            if ($this->claimValidator->validateStep(self::STEP_2_NAME, $data)) {
                return $this->redirectToStep(self::STEP_3_NAME);
            }
        }

        $stepData = $this->getStepData(self::STEP_2_NAME);
        $stepData['summaryMessages'] = $this->getSummaryMessages();
        $stepData['messages'] = $this->claimValidator->getMessages();
        $stepData['questions'] = $this->claimAccountService->getSecurityQuestions();

        $this->layout('layout/layout-govuk.phtml');

        return new ViewModel($stepData);
    }

    /**
     * @return Response|ViewModel
     */
    public function reviewAction()
    {
        $messages = [];

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $this->claimAccountService->captureStep($request->getPost());

            $data = $this->claimAccountService->getSession()->getArrayCopy();

            // Validate step 1 (Confirm Password)
            $isStepOneValid = $this->claimValidator->validateStep(
                self::STEP_1_NAME,
                $data[self::STEP_1_NAME],
                true
            );
            $messages += $this->claimValidator->getMessages();

            // Validate step 2 (Security questions)
            $isStepTwoValid = $this->claimValidator->validateStep(
                self::STEP_2_NAME,
                $data[self::STEP_2_NAME],
                true
            );
            $messages += $this->claimValidator->getMessages();

            if ($isStepOneValid && $isStepTwoValid) {
                try {
                    $this->claimAccountService->sendToApi($data);
                } catch (GeneralRestException $e) {
                    // Data coming from the API is a serialized array due to Frontend API client limitations
                    $apiMessage = unserialize($e->getMessage());
                    if (is_array($apiMessage)
                        && isset($apiMessage['displayMessage'])
                        && isset($apiMessage['step'])
                        && (self::STEP_1_NAME == $apiMessage['step'])) {
                        $this->flashMessenger()->addErrorMessage($apiMessage['displayMessage']);
                    }

                    return $this->redirectToStep(self::STEP_1_NAME);
                }

                $this->claimAccountService->markClaimedSuccessfully();

                return $this->redirectToStep(self::STEP_4_NAME);
            }
        }

        $sessionAsArray = $this->claimAccountService->sessionToArray() ?: [];
        $stepData = $this->getStepData(self::STEP_3_NAME) + $sessionAsArray;
        $stepData['messages'] = $messages;
        $stepData['fullReview'] = $sessionAsArray;

        $reviewViewModel = new ReviewViewModel();
        $reviewViewModel->setData($stepData);
        $reviewViewModel->setSecurityQuestions($this->claimAccountService->getSecurityQuestions());

        $stepData['reviewViewModel'] = $reviewViewModel;

        $this->layout('layout/layout-govuk.phtml');

        return new ViewModel($stepData);
    }

    /**
     * @return ViewModel
     */
    public function successAction()
    {
        $sessionAsArray = $this->claimAccountService->sessionToArray() ?: [];
        $stepData = $this->getStepData(self::STEP_3_NAME) + $sessionAsArray;

        $this->layout('layout/layout-govuk.phtml');

        $vm = new ViewModel($stepData);
        $vm->setTemplate('account/claim/2fa-success');

        return $vm;
    }

    /**
     * To assemble required parameters mainly to be passed to the view model
     *
     * @param string $stepName e.g. self::STEP_1_NAME | self::STEP_2_NAME | self::STEP_3_NAME
     * @return array
     */
    private function getStepData($stepName)
    {
        $this->checkIfPreviousStepsBeenTaken($stepName);

        $stepDataInSession = $this->claimAccountService->getFromSession($stepName);
        $stepData = is_null($stepDataInSession) ? [] : $stepDataInSession;
        $stepData['stepName'] = $stepName;
        $stepData['serviceName'] = self::SERVICE_NAME;
        $stepData['username'] = $this->getIdentity()->getUsername();

        return $stepData;
    }

    /**
     * @param $requestedStep
     */
    private function checkIfPreviousStepsBeenTaken($requestedStep)
    {
        $steps = [
            self::STEP_1_NAME,
            self::STEP_2_NAME,
            self::STEP_3_NAME,
            self::STEP_4_NAME
        ];

        $step = null;

        foreach ($steps as $step) {
            if ($requestedStep === $step) {
                break;
            }

            if (!$this->claimAccountService->isStepRecorded($step)) {
                $this->redirectToStep($step);
                break;
            }
        }
    }

    /**
     * To redirect to different steps
     *
     * @param string $stepName the method name without "Action" e.g.
     *                         to get to generatePinAction we will pass generatePin
     *
     * @return \Zend\Http\Response
     */
    private function redirectToStep($stepName)
    {
        if ($stepName == self::STEP_2_NAME) {
            $url = AccountUrlBuilderWeb::claimSecurityQuestions();
        } elseif ($stepName == self::STEP_3_NAME) {
            $url = AccountUrlBuilderWeb::claimReview();
        } elseif ($stepName == self::STEP_4_NAME) {
            $url = $this->url()->fromRoute('account/claim/success');
        } else {
            $url = AccountUrlBuilderWeb::claimEmailAndPassword();
        }

        return $this->redirect()->toUrl($url);
    }

    /**
     * A workaround for our inconsistent validation messages to keep the correct format in the main validator
     * Claim account will be deprecated or we need to improve at least its validation summary messages
     *
     * @return array
     */
    private function getSummaryMessages()
    {
        $genericErrors = $this->claimValidator->getMessages();

        $errorsSummary = [];

        foreach ($genericErrors as $fieldName => $messages) {
            $errorsSummary[$fieldName] = [];
            foreach ($messages as $validator => $message) {
                switch ($fieldName) {
                    case self::PASSWORD_FIELD:
                        $message = self::NEW_PASSWORD_ERROR_MESSAGE_FIELD_NAME . $message;
                        break;
                    case self::CONFIRM_PASSWORD_FIELD:
                        $message = self::RETYPE_YOUR_NEW_PASSWORD_ERROR_MESSAGE__FIELD_NAME . $message;
                        break;
                    case self::FIRST_MEMORABLE_ANSWER_FIELD:
                        $message = self::FIRST_MEMORABLE_ANSWER_ERROR_MESSAGE_FIELD_NAME . $message;
                        break;
                    case self::SECOND_MEMORABLE_ANSWER_FIELD:
                        $message = self::SECOND_MEMORABLE_ANSWER_ERROR_MESSAGE_FIELD_NAME . $message;
                        break;
                }
                $errorsSummary[$fieldName][$validator] = $message;
            }
        }

        return $errorsSummary;

    }
}
