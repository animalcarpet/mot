<?php

namespace Dashboard\Controller;

use Account\Controller\LockedAccountController;
use Core\Controller\AbstractAuthActionController;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dashboard\Form\ChangePasswordForm;
use Dashboard\Service\PasswordService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Configuration\MotConfig;

class PasswordController extends AbstractAuthActionController
{
    const BACK_LINK = 'change-password-ref';

    private $passwordService;

    protected $form;

    private $identityProvider;

    private $config;

    private $webLogoutService;

    const PAGE_TITLE = 'Change your password';
    const PAGE_SUBTITLE = 'Your profile';

    public function __construct(
        PasswordService $passwordService,
        ChangePasswordForm $form,
        MotFrontendIdentityProviderInterface $identityProvider,
        MotConfig $config,
        WebLogoutService $webLogoutService
    ) {
        $this->passwordService = $passwordService;
        $this->form = $form;
        $this->identityProvider = $identityProvider;
        $this->config = $config;
        $this->webLogoutService = $webLogoutService;
    }

    public function changePasswordAction()
    {
        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageSubTitle', self::PAGE_SUBTITLE);
        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE);
        $this->setHeadTitle(self::PAGE_TITLE);

        $breadcrumbs = [
            'Your profile' => '/your-profile',
            'Change your password' => '',
        ];

        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        $hasPasswordExpired = $this->hasPasswordExpired();
        if ($hasPasswordExpired) {
            $this->layout()->setVariable('pageLede', 'You need to change your password because it has expired');
        }

        $form = $this->form;
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost()->toArray());
            if ($form->isValid()) {
                if ($this->passwordService->changePassword($form->getData())) {
                    if ($hasPasswordExpired) {
                        return $this->successfulPasswordChangePage();
                    } else {
                        return $this->profilePageWithSuccessMessage();
                    }
                }

                $errors = $this->passwordService->getErrors();
                $form->setMessages($errors);

            } elseif ($this->form->isCurrentPasswordNotEmpty()) {
                if ($this->passwordService->shouldWarnUserAboutFailedAttempts()) {
                    return $this->redirect()->toRoute(LockedAccountController::LOCKOUT_WARNING_ROUTE, [],
                        ['query' => ['backTo' => self::BACK_LINK]]
                    );
                }
                elseif ($this->passwordService->isAccountLocked()) {
                    return $this->redirect()->toRoute(LockedAccountController::SET_COOKIE_AND_LOGOUT);
                }
            }

            $form->clearValues();
        }

        $form->obfuscateOldPasswordElementName();

        return [
            'form' => $form,
            'username' => $this->getIdentity()->getUsername(),
            'cancelRoute' => $hasPasswordExpired ? 'logout' : 'newProfile',
            'cancelText' => $hasPasswordExpired ? 'Cancel and return to sign in' : 'Cancel and return to your profile',
        ];
    }

    private function successfulPasswordChangePage()
    {
        $url = ContextProvider::YOUR_PROFILE_PARENT_ROUTE
            .'/change-password/confirmation';

        return $this->redirect()->toRoute(
            $url,
            ['id' => $this->identityProvider->getIdentity()->getUserId()]);
    }

    private function profilePageWithSuccessMessage()
    {
        $this->addSuccessMessage('Your password has been changed.');
        $url = ContextProvider::YOUR_PROFILE_PARENT_ROUTE;

        return $this->redirect()->toRoute($url);
    }

    private function hasPasswordExpired()
    {
        return $this->identityProvider->getIdentity()->hasPasswordExpired()
        && $this->config->get('feature_toggle', 'openam.password.expiry.enabled');
    }

    public function confirmationAction()
    {
        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageSubTitle', 'MOT testing service');
        $this->layout()->setVariable('pageTitle', 'Your password has been changed');

        return [];
    }
}
