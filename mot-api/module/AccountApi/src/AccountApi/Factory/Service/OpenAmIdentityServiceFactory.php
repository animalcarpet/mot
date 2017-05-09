<?php

namespace AccountApi\Factory\Service;

use AccountApi\Service\OpenAmIdentityService;
use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use PersonApi\Service\PasswordExpiryNotificationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for OpenAmIdentityService instances.
 */
class OpenAmIdentityServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return OpenAmIdentityService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $openAMClient = $serviceLocator->get(OpenAMClientInterface::class);
        $realm = $serviceLocator->get(OpenAMClientOptions::class)->getRealm();
        $passwordExpiryNotificationService = $serviceLocator->get(PasswordExpiryNotificationService::class);

        return new OpenAmIdentityService($openAMClient, $passwordExpiryNotificationService, $realm);
    }
}
