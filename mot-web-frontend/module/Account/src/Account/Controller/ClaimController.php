<?php

namespace Account\Controller;

use Account\Service\ClaimAccountService;
use Account\Validator\ClaimValidator;
use Core\Controller\AbstractAuthActionController;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
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

    const STEP_1_NAME = 'confirmEmailAndPassword';
    const STEP_2_NAME = 'setSecurityQuestion';
    const STEP_3_NAME = 'review';
    const STEP_4_NAME = 'success';

    const PIN_ARRAY_KEY = 'pin';

    /** @var ClaimAccountService  */
    private $claimAccountService;
    /** @var  ClaimValidator */
    private $claimValidator;
    /** @var MotIdentityProviderInterface  */
    private $motIdentityProvider;
    /** @var  array */
    private $config;

    public function __construct(
        ClaimAccountService $claimAccountService,
        ClaimValidator $claimValidator,
        MotIdentityProviderInterface $motIdentityProvider,
        $config
    ) {
        $this->claimAccountService = $claimAccountService;
        $this->claimValidator = $claimValidator;
        $this->motIdentityProvider = $motIdentityProvider;
        $this->config = $config;
    }

    public function resetAction()
    {
        $this->claimAccountService->clearSession();
        $this->redirectToStep(self::STEP_1_NAME);
    }

    public function confirmEmailAndPasswordAction()
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
        if (!array_key_exists('email', $stepData)) {
            $stepData['email'] = $stepData['confirm_email'] = $this->claimAccountService->getPresetEmail();
        }

        $stepData['messages'] = $this->claimValidator->getMessages();
        $stepData['summaryMessages'] = $this->getSummaryMessages();
        if ($this->flashMessenger()->hasErrorMessages()) {
            $stepData['messages'] = array_merge($stepData['messages'],
                [array_map('nl2br', $this->flashMessenger()->getErrorMessages())]);
        }

        return new ViewModel($stepData);
    }

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
        $stepData['messages'] = $this->claimValidator->getMessages();
        $stepData['questions'] = $this->claimAccountService->getSecurityQuestions();
        $stepData['helpdeskCfg'] = $this->config['helpdesk'];

        return new ViewModel($stepData);
    }

    public function reviewAction()
    {
        $messages = [];

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $this->claimAccountService->captureStep($request->getPost());

            $data = $this->claimAccountService->getSession()->getArrayCopy();

            //  --  check step 1 (email & password) --
            $isStepOneValid = $this->claimValidator->validateStep(
                self::STEP_1_NAME,
                $data[self::STEP_1_NAME],
                true
            );
            $messages += $this->claimValidator->getMessages();

            //  --  check step 2 (security questions) --
            $isStepTwoValid = $this->claimValidator->validateStep(
                self::STEP_2_NAME,
                $data[self::STEP_2_NAME],
                true
            );
            $messages += $this->claimValidator->getMessages();

            //  --  send to api for store    --
            if ($isStepOneValid && $isStepTwoValid) {
                try {
                    $this->claimAccountService->sendToApi($data);
                } catch (GeneralRestException $e) {
                    // Data coming from the API is a serialized array due to Frontend API client limitations.
                    $apiMessage = unserialize($e->getMessage());
                    if (is_array($apiMessage)
                        && isset($apiMessage['displayMessage'])
                        && isset($apiMessage['step'])
                        && ('confirmEmailAndPassword' == $apiMessage['step'])) {
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
        $stepData['isStepOneInvalid'] = isset($isStepOneValid) && !$isStepOneValid;
        $stepData['isStepTwoInvalid'] = isset($isStepTwoValid) && !$isStepTwoValid;
        $stepData['helpdeskCfg'] = $this->config['helpdesk'];

        $reviewViewModel = new ReviewViewModel();
        $reviewViewModel->setData($stepData);
        $reviewViewModel->setSecurityQuestions($this->claimAccountService->getSecurityQuestions());

        $stepData['reviewViewModel'] = $reviewViewModel;

        return new ViewModel($stepData);
    }


    public function successAction()
    {
        $sessionAsArray = $this->claimAccountService->sessionToArray() ?: [];
        $stepData = $this->getStepData(self::STEP_3_NAME) + $sessionAsArray;

        $is2FA = $this->motIdentityProvider->getIdentity()->isSecondFactorRequired();

        $vm = new ViewModel($stepData);
        $vm->setTemplate($is2FA ? 'account/claim/2fa-success' : 'account/claim/pin-success');
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
     * @return array
     */
    private function getSummaryMessages()
    {
        $genericErrors = $this->claimValidator->getMessages();

        $errorsSummary = [];

        foreach ($genericErrors as $fieldName => $messages) {
            $errorsSummary[$fieldName] = [];
            foreach ($messages as $validator => $message) {
                if ('password' == $fieldName) {
                    $message = 'Password ' . $message;
                }
                $errorsSummary[$fieldName][$validator] = $message;
            }
        }

        return $errorsSummary;

    }
}
