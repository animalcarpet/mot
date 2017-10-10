<?php

namespace Dashboard\Factory\Service;

use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client;
use Dashboard\Service\PasswordService;

class PasswordServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PasswordService(
            $serviceLocator->get(Client::class),
            $serviceLocator->get(OpenAMClientInterface::class),
            $serviceLocator->get(OpenAMClientOptions::class),
            $serviceLocator->get('MotIdentityProvider')
        );
    }
}
