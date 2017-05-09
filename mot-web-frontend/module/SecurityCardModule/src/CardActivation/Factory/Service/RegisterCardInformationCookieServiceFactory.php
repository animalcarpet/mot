<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardInformationCookieService;
use Core\Service\MotFrontendIdentityProvider;

class RegisterCardInformationCookieServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $identityProvider MotFrontendIdentityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        return new RegisterCardInformationCookieService($identityProvider);
    }
}
