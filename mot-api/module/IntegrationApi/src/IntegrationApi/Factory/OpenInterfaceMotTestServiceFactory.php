<?php

namespace IntegrationApi\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use IntegrationApi\OpenInterface\Repository\OpenInterfaceMotTestRepository as OpenInterfaceMotTestRepository;
use IntegrationApi\OpenInterface\Service\OpenInterfaceMotTestService;

class OpenInterfaceMotTestServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new OpenInterfaceMotTestService($serviceLocator->get(OpenInterfaceMotTestRepository::class));
    }
}
