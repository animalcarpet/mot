<?php

namespace Application\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Service\LoggedInUserManager;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

class LoggedInUserManagerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $motSession = $serviceLocator->get('MotSession');

        return new LoggedInUserManager(
            $serviceLocator->get('MotIdentityProvider'),
            $serviceLocator->get('AuthorisationService'),
            $motSession,
            $serviceLocator->get(HttpRestJsonClient::class)
        );
    }
}
