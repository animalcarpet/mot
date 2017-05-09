<?php

namespace DvsaAuthentication\Authentication\Adapter\OpenAM\Factory;

use DvsaAuthentication\Authentication\Adapter\OpenAM\OpenAMApiTokenBasedAdapter;
use DvsaAuthentication\Identity\OpenAM\OpenAMIdentityByTokenResolver;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OpenAMApiTokenBasedAdapterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $identityByTokenResolver = $serviceLocator->get(OpenAMIdentityByTokenResolver::class);
        $logger = $serviceLocator->get('Application\Logger');
        $tokenService = $serviceLocator->get('tokenService');

        $adapter = new OpenAMApiTokenBasedAdapter(
            $identityByTokenResolver,
            $logger,
            $tokenService
        );

        return $adapter;
    }
}
