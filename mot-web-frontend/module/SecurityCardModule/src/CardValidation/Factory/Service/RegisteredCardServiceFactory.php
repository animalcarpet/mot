<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Factory\Service;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\RegisteredCardService;
use Zend\Authentication\AuthenticationService;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;

class RegisteredCardServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var AuthenticationService $authenticationService */
        $authenticationService = $serviceLocator->get('ZendAuthenticationService');

        /** @var $authorisationService AuthorisationService */
        $authorisationService = $serviceLocator->get(AuthorisationService::class);

        return new RegisteredCardService($authenticationService, $authorisationService);
    }
}