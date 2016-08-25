<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Listener;

use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionInSystem;
use Zend\Mvc\MvcEvent;
use Zend\View\Helper\Identity;
use Zend\Authentication\AuthenticationService;
use Core\Service\LazyMotFrontendAuthorisationService;
use DvsaFeature\FeatureToggles;

class CardPinValidationListener
{
    /**
     * @var array
     */
    private $whiteList = [
        '@login-2fa@',
        '@login@',
        '@logout@',
        '@account-register/create-an-account@',
        '@account-register/create-an-account/(.*)@',
        '@forgotten-password@',
        '@forgotten-password/(.*)@',
        '@lost-or-forgotten-card@',
        '@account/claim@',
        '@account/claim/(.*)@'
    ];

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var LazyMotFrontendAuthorisationService
     */
    private $authorisationService;

    /**
     * Validate2FAPinListener constructor.
     * @param AuthenticationService $authenticationService
     * @param MotIdentityProviderInterface $motIdentityProviderInterface
     * @param LazyMotFrontendAuthorisationService $authorisationService
     * @param TwoFaFeatureToggle $twoFaFeatureToggle
     */
    public function __construct
    (
        AuthenticationService $authenticationService,
        MotIdentityProviderInterface $motIdentityProviderInterface,
        LazyMotFrontendAuthorisationService $authorisationService,
        TwoFaFeatureToggle $twoFaFeatureToggle
    )
    {
        $this->authenticationService = $authenticationService;
        $this->identityProvider = $motIdentityProviderInterface;
        $this->authorisationService = $authorisationService;
        $this->featureToggle =  $twoFaFeatureToggle;
    }

    public function __invoke(MvcEvent $event)
    {
        $is2FaEnabled = $this->featureToggle->isEnabled();
        $routeName = $event->getRouteMatch()->getMatchedRouteName();
        $redirectUrl = null;

        if ($is2FaEnabled) {
            $hasIdentity = $this->authenticationService->hasIdentity();
            $has2FAPermission = $this->authorisationService->isGranted(PermissionInSystem::AUTHENTICATE_WITH_2FA);

            if ($hasIdentity) {
                $authenticatedWith2FA = $this->authenticationService->getIdentity()->isAuthenticatedWith2FA();
                $isCardActivated = $this->authenticationService->getIdentity()->isSecondFactorRequired();
            } else {
                $authenticatedWith2FA = false;
                $isCardActivated = false;
            }

            if ($this->isRouteRestricted($routeName) && !$authenticatedWith2FA && $has2FAPermission && $isCardActivated) {
                $redirectUrl = $event->getRouter()->assemble([], ['name' => 'login-2fa']);
            }
        }

        if ($redirectUrl) {
            $response = $event->getResponse();
            $response->getHeaders()->addHeaderLine('Location', $redirectUrl);
            $response->setStatusCode(302);
            $response->sendHeaders();
            $event->stopPropagation();
        }
    }

    /**
     *
     * @param string $routeName
     * @return bool
     */
    private function isRouteRestricted($routeName)
    {
        foreach ($this->whiteList as $preg) {
            if (preg_match($preg, $routeName) > 0) {
                return false;
            }
        }

        return true;
    }
}