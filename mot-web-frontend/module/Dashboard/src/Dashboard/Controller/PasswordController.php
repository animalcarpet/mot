<?php

namespace Dashboard\Controller;

use Core\Controller\AbstractAuthActionController;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dashboard\Form\ChangePasswordForm;
use Dashboard\Service\PasswordService;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Constants\FeatureToggle;

class PasswordController extends AbstractAuthActionController
{
    private $passwordService;

    protected $form;

    private $identityProvider;

    private $config;

    public function __construct(
        PasswordService $passwordService,
        ChangePasswordForm $form,
        MotFrontendIdentityProviderInterface $identityProvider,
        MotConfig $config
    ) {
        $this->passwordService = $passwordService;
        $this->form = $form;
        $this->identityProvider = $identityProvider;
        $this->config = $config;
    }

    public function changePasswordAction()
    {
        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageSubTitle', "Your profile");
        $this->layout()->setVariable('pageTitle', "Change your password");
        $breadcrumbs = [
            'Your profile'         => $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ? '/your-profile' : '/profile',
            'Change your password' => '',
        ];

        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        $hasPasswordExpired = $this->hasPasswordExpired();
        if ($hasPasswordExpired) {
            $this->layout()->setVariable('pageLede', "You need to change your password because it has expired");
        }

        $form = $this->form;
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost()->toArray());
            if ($form->isValid()) {
                if ($this->passwordService->changePassword($form->getData())) {
                    if ($hasPasswordExpired) {
                        $url = $this->isFeatureEnabled(
                            FeatureToggle::NEW_PERSON_PROFILE
                        )
                            ? ContextProvider::YOUR_PROFILE_PARENT_ROUTE
                                . '/change-password/confirmation'
                            : 'user-home/profile/change-password/confirmation';
                        return  $this->isFeatureEnabled(
                            FeatureToggle::NEW_PERSON_PROFILE
                        )
                        ? $this->redirect()->toRoute(
                            $url,
                            ['id' => $this->identityProvider->getIdentity()->getUserId()]
                        )
                        : $this->redirect()->toRoute($url);
                    } else {
                        $this->addSuccessMessage("Your password has been changed.");
                        $url = $this->isFeatureEnabled(
                            FeatureToggle::NEW_PERSON_PROFILE
                        ) 
                            ? ContextProvider::YOUR_PROFILE_PARENT_ROUTE
                            : 'user-home/profile/byId';

                        return $this->redirect()->toRoute($url);
                    }
                }

                $errors = $this->passwordService->getErrors();
                $form->setMessages($errors);
            }

            $form->clearValues();
        }

        $form->obfuscateOldPasswordElementName();

        $newProfileEnabled = $this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE);

        return [
            'form'        => $form,
            'username'    => $this->getIdentity()->getUsername(),
            'cancelRoute' => $hasPasswordExpired ? "logout" : ($this->isFeatureEnabled(FeatureToggle::NEW_PERSON_PROFILE) ? 'newProfile' : 'user-home/profile/byId'),
            'cancelText'  => $hasPasswordExpired ? "Cancel and return to sign in" : "Cancel and return to your profile",
            'newProfileEnabled' => $newProfileEnabled,
        ];
    }

    private function hasPasswordExpired()
    {
        return $this->identityProvider->getIdentity()->hasPasswordExpired()
        && $this->config->get('feature_toggle', 'openam.password.expiry.enabled');
    }

    public function confirmationAction()
    {
        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageSubTitle', "MOT testing service");
        $this->layout()->setVariable('pageTitle', "Your password has been changed");

        return [];
    }
}
