<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Controller;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegisterUserService;
use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use Core\Service\StepService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\DetailsStep;
use Dvsa\Mot\Frontend\RegistrationModule\Step\EmailStep;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommon\InputFilter\Registration\EmailInputFilter;
use Zend\View\Model\ViewModel;

/**
 * Completed Controller.
 */
class CompletedController extends RegistrationBaseController
{
    const PAGE_TITLE = 'Your account has been created';
    const PAGE_TITLE_FAILURE = 'Your account has not been created';
    const SESSION_CHECK = "SESSION_CHECK";
    const SESSION_RESULT = "SESSION_RESULT";

    /**
     * @var RegisterUserService
     */
    protected $registerUserService;

    /**
     * @var RegistrationSessionService
     */
    protected $session;

    private $helpdeskConfig;

    /**
     * @param StepService                $stepService
     * @param RegisterUserService        $registerUserService
     * @param RegistrationSessionService $session
     */
    public function __construct(
        StepService $stepService,
        RegisterUserService $registerUserService,
        RegistrationSessionService $session,
        array $helpdeskConfig
    ) {
        parent::__construct($stepService);
        $this->registerUserService = $registerUserService;
        $this->session = $session;
        $this->helpdeskConfig = $helpdeskConfig;
    }


    /**
     * To stop dbl clicks on the registration button we only process the request once.
     *
     * @return \Zend\Http\Response
     */
    public function indexAction()
    {
        $container = $this->session->load(self::SESSION_CHECK);

        if (is_array($container) && count($container) == 0) {

            $this->session->save(self::SESSION_CHECK,['PreviousSubmission'=>true]);

            $userCreated = (bool) $this->registerUserService->registerUser($this->session->toArray());

            $this->session->save(self::SESSION_RESULT,['UserCreated'=>$userCreated]);

            return $this->redirectToRoute($userCreated);
        }

        // Handle double clicks
        $resultContainer = $this->session->load(self::SESSION_RESULT);
        if (is_array($resultContainer) && count($resultContainer) > 0 && isset($resultContainer['UserCreated'])) {
            return $this->redirectToRoute($resultContainer['UserCreated']);
        }
    }

    /**
     * redirect to the success or failure route
     *
     * @param bool $result
     * @return \Zend\Http\Response
     */
    protected function redirectToRoute($result)
    {
        if ($result === true) {
            return $this->redirect()->toRoute('account-register/complete-registration-success');
        } else {
            return $this->redirect()->toRoute('account-register/complete-registration-failure');
        }
    }

    /**
     * This is the end of the journey.. kill the session and were done.
     *
     * The email address will only get displayed to the user once, if they refresh the page it will go
     *
     * @return ViewModel
     */
    public function successAction()
    {
        if (false === $this->checkValidSession()) {
            $this->redirectToStart();
        }

        $this->setLayout(self::PAGE_TITLE);

        $values = $this->session->toArray();

        $emailAddress = (isset($values[EmailStep::STEP_ID][EmailInputFilter::FIELD_EMAIL]))
            ? $values[EmailStep::STEP_ID][EmailInputFilter::FIELD_EMAIL]
            : null;

        $this->session->destroy();

        return new ViewModel([
            'email'     => $emailAddress,
            'helpdesk'  => $this->helpdeskConfig,
        ]);
    }

    /**
     * Do NOT kill the session on fail. The user has the ability to go
     * back and modify settings.
     *
     * @return ViewModel
     */
    public function failAction()
    {
        if (false === $this->checkValidSession()) {
            $this->redirectToStart();
        }

        $this->setLayout(self::PAGE_TITLE_FAILURE, self::DEFAULT_SUB_TITLE);

        return new ViewModel([
            'helpdesk'  => $this->helpdeskConfig,
        ]);
    }

    /**
     * If there is no valid session, we should go to the journey start.
     *
     * @return \Zend\Http\Response
     */
    protected function checkValidSession()
    {
        $values = $this->session->toArray();

        return !(is_array($values) && count($values) === 0);
    }

    protected function redirectToStart()
    {
        return $this->redirect()->toRoute('account-register/create-an-account');
    }
}
