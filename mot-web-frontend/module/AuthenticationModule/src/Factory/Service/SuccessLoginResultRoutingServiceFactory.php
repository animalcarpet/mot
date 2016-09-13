<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service;

use Core\Service\LazyMotFrontendAuthorisationService;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\GotoUrlService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\SuccessLoginResultRoutingService;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SuccessLoginResultRoutingServiceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var AuthenticationService $authenticationService */
        $authenticationService = $serviceLocator->get('ZendAuthenticationService');

        /** @var  LazyMotFrontendAuthorisationService $authorisationService */
        $authorisationService = $serviceLocator->get('AuthorisationService');

        /** @var AuthorisationService $authorisationServiceClient */
        $authorisationServiceClient = $serviceLocator->get(AuthorisationService::class);

        /** @var GotoUrlService $gotoUrlService */
        $gotoUrlService = $serviceLocator->get(GotoUrlService::class);

        /** @var TwoFaFeatureToggle $twoFactorFeatureToggle */
        $twoFactorFeatureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);

        return new SuccessLoginResultRoutingService(
            $authorisationServiceClient,
            $authenticationService,
            $authorisationService,
            $gotoUrlService,
            $twoFactorFeatureToggle
        );
    }
}