<?php

namespace DvsaAuthentication\Login\Factory;

use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use DvsaAuthentication\Identity\IdentityByTokenResolver;
use DvsaAuthentication\Login\OpenAM\OpenAMAuthenticator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticatorFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $openAMClient = $serviceLocator->get(OpenAMClientInterface::class);
        $openAMOptions = $serviceLocator->get(OpenAMClientOptions::class);
        $logger = $serviceLocator->get('Application\Logger');
        $identityByTokenResolver = $serviceLocator->get(IdentityByTokenResolver::class);

        return new OpenAMAuthenticator(
            $openAMClient,
            $openAMOptions,
            $identityByTokenResolver,
            $logger
        );
    }
}
