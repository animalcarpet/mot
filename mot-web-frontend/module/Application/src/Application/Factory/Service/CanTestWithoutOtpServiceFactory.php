<?php

namespace Application\Factory\Service;

use Application\Service\CanTestWithoutOtpService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CanTestWithoutOtpServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CanTestWithoutOtpService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $identityProvider = $serviceLocator->get('MotIdentityProvider');
        $authorisationService = $serviceLocator->get('AuthorisationService'); // this is deprecated, use something else?
        $twoFaFeatureToggle = $serviceLocator->get(TwoFaFeatureToggle::class);

        return new CanTestWithoutOtpService($identityProvider, $authorisationService, $twoFaFeatureToggle);
    }
}
