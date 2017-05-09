<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Service;

use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RegisterCardServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $authorisationServiceClient AuthorisationService */
        $authorisationServiceClient = $serviceLocator->get(AuthorisationService::class);

        /** @var $identityProvider MotFrontendIdentityProvider */
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        return new RegisterCardService(
            $authorisationServiceClient,
            $identityProvider
        );
    }
}
