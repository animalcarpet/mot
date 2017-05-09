<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Factory\Listener;

use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Listener\CardPinValidationListener;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CardPinValidationListenerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var AuthenticationService $authenticationService */
        $authenticationService = $serviceLocator->get('ZendAuthenticationService');

        /** @var MotIdentityProviderInterface $motIdentityProvider */
        $motIdentityProvider = $serviceLocator->get('MotIdentityProvider');

        $authorisationService = $serviceLocator->get(MotAuthorisationServiceInterface::class);

        $twoFactorFeatureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);

        return new CardPinValidationListener($authenticationService, $motIdentityProvider, $authorisationService, $twoFactorFeatureToggle);
    }
}
