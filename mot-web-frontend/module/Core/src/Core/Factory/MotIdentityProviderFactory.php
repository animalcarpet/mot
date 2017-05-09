<?php

namespace Core\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Core\Service\MotFrontendIdentityProvider;

class MotIdentityProviderFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new MotFrontendIdentityProvider($serviceLocator->get('ZendAuthenticationService'));
    }
}
