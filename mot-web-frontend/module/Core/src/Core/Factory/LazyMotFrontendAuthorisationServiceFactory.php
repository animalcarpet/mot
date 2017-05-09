<?php

namespace Core\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Core\Service\LazyMotFrontendAuthorisationService;
use DvsaCommon\HttpRestJson\Client;

class LazyMotFrontendAuthorisationServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new LazyMotFrontendAuthorisationService(
            $serviceLocator->get('MotIdentityProvider'),
            $serviceLocator->get(Client::class)
        );
    }
}
