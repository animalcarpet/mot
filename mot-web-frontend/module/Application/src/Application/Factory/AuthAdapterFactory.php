<?php

namespace Application\Factory;

use DvsaCommon\HttpRestJson\Client;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\AuthAdapter\Rest as AuthAdapter;

class AuthAdapterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $restClient = $serviceLocator->get(Client::class);
        $tokenService = $serviceLocator->get('tokenService');
        $authAdapter = new AuthAdapter($restClient, $tokenService);

        return $authAdapter;
    }
}
