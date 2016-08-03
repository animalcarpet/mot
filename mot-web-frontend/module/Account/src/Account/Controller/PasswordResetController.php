<?php

namespace Account\Controller;

use Account\Service\PasswordResetService;
use Account\ViewModel\ChangePasswordFormModel;
use Account\ViewModel\PasswordResetFormModel;
use Core\Controller\AbstractAuthActionController;
use DvsaClient\MapperFactory;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Account\MessageDto;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * Class PasswordResetController
 * @package Account\Controller
 */
class PasswordResetController extends AbstractAuthActionController
{
    const CFG_PASSWORD_RESET = 'password_reset';
    const CFG_PASSWORD_RESET_EXPIRE_TIME = 'expireTime';

    const PAGE_TITLE = 'Forgotten password';
    const PAGE_SUBTITLE = 'MOT testing service';
    const PAGE_TITLE_CONFIRMATION = 'Success!';
    const PAGE_TITLE_FAILURE = 'Forgotten security question(s)';
    const PAGE_TITLE_EMAIL_NOT_FOUND = 'Email address not found';
    const PAGE_TITLE_PASSWORD_RESET = 'Reset your password';

    const STEP_1 = 'Step 1 of 3';
    const STEP_2 = 'Step 2 of 3';
    const STEP_3 = 'Step 3 of 3';
    const STEP_4 = 'User authenticated and email sent';
    const STEP_FAIL = 'Call Helpdesk';

    const QUESTION_1 = 1;

    const ERR_CHANGE_PASS_TOKEN_NOT_FOUND = 'Your password reset link is invalid.';
    const ERR_CHANGE_PASS_TOKEN_INVALID = 'Your password reset link has now expired. Please click the link below to reauthenticate and send another password reset link.';
    const ERR_CHANGE_PASS_TOKEN_BEEN_USED = 'Your password reset link has already been used to reset your password. If you have any questions please contact the %s on %s.';
    const ERR_CHANGE_PASS_USER_DISABLED = 'The user account is not known';
    const ERR_PASSWORD_NOT_VALID = 'Password contains invalid characters, please try again';

    const TEXT_LINK_EXPIRED = 'The password reset link has expired.';
    const TEXT_LINK_BEEN_USED = 'The password reset link has already been used.';
    const TEXT_YOU_HAVE_ARRIVED_HERE = 'You have arrived here after entering/following a link to reset your password';
    const TEXT_YOU_MUST_CHANGE_PWORD = 'Your password has expired. Change it now.';

    /** @var PasswordResetService */
    protected $passwordResetService;
    /** @var UserAdminSessionManager */
    protected $userAdminSessionManager;
    /** @var MapperFactory */
    protected $mapperFactory;
    /** @var  array */
    protected $config;
    /** @var ParamObfuscator  */
    protected $obfuscator;

    /** @var PasswordResetFormModel */
    protected $view;

    public function __construct(
        PasswordResetService $passwordResetService,
        UserAdminSessionManager $userAdminSessionManager,
        MapperFactory $mapperFactory,
        $config,
        ParamObfuscator $obfuscator
    ) {
        $this->passwordResetService = $passwordResetService;
        $this->userAdminSessionManager = $userAdminSessionManager;
        $this->mapperFactory = $mapperFactory;
        $this->config = $config;
        $this->obfuscator = $obfuscator;
    }

    /**
     * This action allow us to ask the username of the user and check if it is valid
     *
     * @return ViewModel
     */
    public function usernameAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        $this->view = new PasswordResetFormModel();

        $this->userAdminSessionManager->deleteUserAdminSession();

        if ($request->isPost()) {
            $this->view->populateFromPost($request->getPost()->toArray());

            if ($this->view->isValid()) {
                try {
                    $personId = $this->passwordResetService->validateUsername($this->view->getUsername());
                    if ($personId === false) {
                        return $this->redirect()->toUrl(AccountUrlBuilderWeb::forgottenPasswordEmailNotFound());
                    }

                    return $this->redirect()->toUrl(
                        AccountUrlBuilderWeb::forgottenPasswordSecurityQuestion(
                            $personId,
                            UserAdminSessionManager::FIRST_QUESTION
                        )
                    );
                } catch (NotFoundException $e) {
                    $this->view->addError(
                        PasswordResetFormModel::FIELD_USERNAME, PasswordResetFormModel::USER_NOT_FOUND
                    );
                }
            }
        }

        return $this->initViewModelInformation(self::PAGE_TITLE, self::PAGE_SUBTITLE, self::STEP_1);
    }

    /**
     * This action verify if the user is authenticate and send the forgotten password email
     *
     * @return Response
     */
    public function authenticatedAction()
    {
        $success = $this->userAdminSessionManager->isUserAuthenticated(
            $this->userAdminSessionManager->getElementOfUserAdminSession(UserAdminSessionManager::USER_KEY)
        );

        if ($success !== true) {
            return $this->redirect()->toUrl(AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated());
        }

        if (!$this->userAdminSessionManager->getElementOfUserAdminSession(UserAdminSessionManager::EMAIL_SENT)) {
            try {
                $this->mapperFactory->Account->resetPassword(
                    $this->userAdminSessionManager->getElementOfUserAdminSession(UserAdminSessionManager::USER_KEY)
                );
                $this->userAdminSessionManager->updateUserAdminSession(UserAdminSessionManager::EMAIL_SENT, true);
                return $this->redirect()->toUrl(AccountUrlBuilderWeb::forgottenPasswordConfirmation());
            } catch (\Exception $e) {
                $this->addErrorMessage($e->getMessage());
            }
        } else {
            return $this->redirect()->toUrl(AccountUrlBuilderWeb::forgottenPasswordConfirmation());
        }

        return $this->redirect()->toUrl(AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated());
    }

    /**
     * This action verify if the user is authenticate and show the confirmation page
     *
     * @return ViewModel
     */
    public function emailNotFoundAction()
    {
        $success = $this->userAdminSessionManager->isUserAuthenticated(
            $this->userAdminSessionManager->getElementOfUserAdminSession(UserAdminSessionManager::USER_KEY)
        );

        if ($success === true) {
            return $this->redirect()->toUrl(AccountUrlBuilderWeb::forgottenPasswordAuthenticated());
        }

        $this->view = $this->config;

        return $this->initViewModelInformation(
            self::PAGE_TITLE_EMAIL_NOT_FOUND,
            self::PAGE_SUBTITLE
        );
    }

    /**
     * This action verify if the user is authenticate and show the confirmation page
     *
     * @return ViewModel
     */
    public function notAuthenticatedAction()
    {
        $success = $this->userAdminSessionManager->isUserAuthenticated(
            $this->userAdminSessionManager->getElementOfUserAdminSession(UserAdminSessionManager::USER_KEY)
        );

        if ($success === true) {
            return $this->redirect()->toUrl(AccountUrlBuilderWeb::forgottenPasswordAuthenticated());
        }

        $this->view = $this->config;

        return $this->initViewModelInformation(
            self::PAGE_TITLE_FAILURE,
            self::PAGE_SUBTITLE,
            self::STEP_FAIL
        );
    }

    /**
     * This action verify if the user is authenticate and show the confirmation page
     *
     * @return ViewModel
     */
    public function confirmationAction()
    {
        $success = $this->userAdminSessionManager->isUserAuthenticated(
            $this->userAdminSessionManager->getElementOfUserAdminSession(UserAdminSessionManager::USER_KEY)
        );

        if ($success !== true) {
            return $this->redirect()->toUrl(AccountUrlBuilderWeb::forgottenPasswordNotAuthenticated());
        }

        $this->view = new PasswordResetFormModel();
        $this->view->setCfgExpireTime($this->config[self::CFG_PASSWORD_RESET][self::CFG_PASSWORD_RESET_EXPIRE_TIME]);
        $this->view->setConfig($this->config);

        return $this->initViewModelInformation(self::PAGE_TITLE_CONFIRMATION, self::PAGE_SUBTITLE, self::STEP_4);
    }

    public function changePasswordAction()
    {
        $token = $this->params('resetToken');

        if (empty($token)) {
            // trigger page not found
            throw new NotFoundException('', '', [], 404);
        }

        //  --  check and get token --
        $tokenDto = $this->passwordResetService->getToken($token);

        $expiryDateTs = null;
        $currentDateTs = (new DateTimeHolder)->getCurrent()->getTimestamp();
        if ($tokenDto instanceof MessageDto) {
            $expiryDateTs = DateUtils::toDateTime($tokenDto->getExpiryDate())->getTimestamp();
        }

        $this->view = new ChangePasswordFormModel();
        $this->layout()->setVariable('pageLede', self::TEXT_YOU_HAVE_ARRIVED_HERE);

        $flashMsgr = $this->flashMessenger();

        if ($tokenDto === null) {
            //  --  token invalid or not found  --
            $flashMsgr->addErrorMessage(self::ERR_CHANGE_PASS_TOKEN_NOT_FOUND);

        } elseif ($tokenDto->isAcknowledged()) {
            //  --  token was already used  --
            $this->layout()->setVariable('pageLede', self::TEXT_LINK_BEEN_USED);

            $helpdeskCfg = $this->getConfig()['helpdesk'];

            $flashMsgr->addErrorMessage(
                sprintf(self::ERR_CHANGE_PASS_TOKEN_BEEN_USED, $helpdeskCfg['name'], $helpdeskCfg['phoneNumber'])
            );

        } elseif (!$tokenDto->hasPerson()) {
            //  --  person account expired or was disabled  --
            $flashMsgr->addErrorMessage(self::ERR_CHANGE_PASS_USER_DISABLED);

        } elseif (isset($expiryDateTs) && $expiryDateTs < $currentDateTs) {
            //  --  token expired   --
            $flashMsgr->addErrorMessage(self::ERR_CHANGE_PASS_TOKEN_INVALID);

            $this->layout()->setVariable('pageLede', self::TEXT_LINK_EXPIRED);

            $this->view->setTryAgainLink(true);
        }

        if (!$flashMsgr->hasCurrentErrorMessages()) {
            /** @var Request $request */
            $request = $this->getRequest();

            //  --  form    --
            $this->view->setUsername($tokenDto->getPerson()->getUsername());
            $data = $request->getPost()->toArray();

            if ($request->isPost()) {
                $this->view->populateFromPost($request->getPost()->toArray());

                if ($this->view->isValid()) {
                    try {
                        $this->getRestClient()->post(
                            'account/password-change',
                            [
                                'token'       => $token,
                                'newPassword' => $data['password']
                            ]
                        );

                        return $this->redirect()->toUrl(PersonUrlBuilderWeb::home());
                    } catch (ValidationException $e) {
                        $passwordError = ArrayUtils::first($e->getErrors())['message'];
                        $this->view->addError(ChangePasswordFormModel::FIELD_PASS, $passwordError);
                    }
                }
            }
        }

        return $this->initViewModelInformation(self::PAGE_TITLE_PASSWORD_RESET, self::PAGE_SUBTITLE);
    }

    public function updatePasswordAction()
    {
        $this->view = new ChangePasswordFormModel();
        $this->layout()->setVariable('pageLede', self::TEXT_YOU_MUST_CHANGE_PWORD);

        if (!$this->flashMessenger()->hasCurrentErrorMessages()) {
            /** @var Request $request */
            $request = $this->getRequest();

            //  --  form    --
            $this->view->setUsername($this->getIdentity()->getUsername());
            $data = $request->getPost()->toArray();

            if ($request->isPost()) {
                $this->view->populateFromPost($request->getPost()->toArray());

                if ($this->view->isValid()) {
                    try {
                        $this->getRestClient()->put(
                            'account/password-update/' . $this->getIdentity()->getUserId(),
                            [
                                'password' => $this->obfuscator->obfuscate($data['password'])
                            ]
                        );
                        $this->getServiceLocator()->get('ZendAuthenticationService')->clearIdentity();

                        return $this->redirect()->toUrl(PersonUrlBuilderWeb::home());
                    } catch (ValidationException $e) {
                        $passwordError = ArrayUtils::first($e->getErrors())['message'];
                        $this->view->addError(ChangePasswordFormModel::FIELD_PASS, $passwordError);
                    }
                }
            }
        }

        return $this->initViewModelInformation(self::PAGE_TITLE_PASSWORD_RESET, self::PAGE_SUBTITLE);
    }

    /**
     * This function initialize the template
     *
     * @param string $title
     * @param string $subtitle
     * @param string $step
     *
     * @return ViewModel
     */
    private function initViewModelInformation($title, $subtitle, $step = null)
    {
        $this->layout('layout/layout-govuk.phtml');

        $this->layout()->setVariable('pageSubTitle', $subtitle);
        $this->layout()->setVariable('pageTitle', $title);

        if ($step !== null) {
            $this->layout()->setVariable('progress', $step);
        }

        return new ViewModel(
            [
                'viewModel' => $this->view,
                'isLoggedIn' => ($this->getIdentity() !== null)
            ]
        );
    }
}
