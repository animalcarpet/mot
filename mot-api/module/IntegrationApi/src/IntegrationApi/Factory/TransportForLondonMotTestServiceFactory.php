<?php

namespace IntegrationApi\Factory;

use DvsaEntities\Repository\MotTestRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use IntegrationApi\TransportForLondon\Service\TransportForLondonMotTestService;

class TransportForLondonMotTestServiceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new TransportForLondonMotTestService($serviceLocator->get(MotTestRepository::class));
    }
}
