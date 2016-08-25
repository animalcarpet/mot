<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Factory\Controller;

use Dvsa\Mot\Frontend\AuthenticationModule\Controller\SecurityController;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\AuthenticationAccountLockoutViewModelBuilder;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\GotoUrlService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\IdentitySessionStateService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\LoginCsrfCookieService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLoginService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\SessionManager;

/**
 * Factory for SecurityController instances.
 */
class SecurityControllerFactory implements FactoryInterface
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return \Dvsa\Mot\Frontend\AuthenticationModule\Controller\SecurityController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $request = $serviceLocator->get('Request');
        $response = $serviceLocator->get('Response');
        $gotoUrlService = $serviceLocator->get(GotoUrlService::class);
        $identitySessionStateService = $serviceLocator->get(IdentitySessionStateService::class);
        $loginCsrfCookieService = $serviceLocator->get(LoginCsrfCookieService::class);
        $authenticationService = $serviceLocator->get('ZendAuthenticationService');
        $loginService = $serviceLocator->get(WebLoginService::class);
        $authenticationAccountLockoutViewModelBuilder = $serviceLocator->get(AuthenticationAccountLockoutViewModelBuilder::class);
        $twoFactorFeatureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);

        return new SecurityController(
            $request,
            $response,
            $gotoUrlService,
            $identitySessionStateService,
            $loginService,
            $loginCsrfCookieService,
            $authenticationService,
            $authenticationAccountLockoutViewModelBuilder,
            $twoFactorFeatureToggle
        );
    }
}
