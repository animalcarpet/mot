<?php

namespace Dashboard\Factory\Service;

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
            $serviceLocator->get('MotIdentityProvider')
        );
    }
}
