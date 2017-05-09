<?php

namespace DvsaAuthentication\Identity\Factory;

use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use DvsaAuthentication\Authentication\Adapter\OpenAM\OpenAMCachedClient;
use DvsaAuthentication\Factory\IdentityFactoryFactory;
use DvsaAuthentication\Identity\OpenAM\OpenAMIdentityByTokenResolver;
use DvsaCommon\Configuration\MotConfig;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IdentityByTokenResolverFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $openAMOptions = $serviceLocator->get(OpenAMClientOptions::class);
        $openAMClient = $this->getOpenAMClient($serviceLocator);
        $identityFactory = $serviceLocator->get(IdentityFactoryFactory::class);
        $logger = $serviceLocator->get('Application\Logger');

        return new OpenAMIdentityByTokenResolver($openAMClient, $openAMOptions, $logger, $identityFactory);
    }

    private function getOpenAMClient(ServiceLocatorInterface $serviceLocator)
    {
        $openAMClientClass = $this->isCacheEnabled($serviceLocator)
            ? OpenAMCachedClient::class
            : OpenAMClientInterface::class;

        return $serviceLocator->get($openAMClientClass);
    }

    private function isCacheEnabled(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotConfig $config */
        $config = $serviceLocator->get(MotConfig::class);

        return $config->withDefault(false)->get('cache', 'open_am_client', 'enabled');
    }
}
