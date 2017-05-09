<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Factory\Service;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\PasswordValidationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client;

class PasswordValidationServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var Client $client */
        $client = $serviceLocator->get(Client::class);

        return new PasswordValidationService($client);
    }
}
