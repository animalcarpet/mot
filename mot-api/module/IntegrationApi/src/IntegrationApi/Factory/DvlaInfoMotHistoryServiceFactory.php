<?php

namespace IntegrationApi\Factory;

use DvsaEntities\Repository\MotTestRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use IntegrationApi\DvlaInfo\Service\DvlaInfoMotHistoryService;

class DvlaInfoMotHistoryServiceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new DvlaInfoMotHistoryService($serviceLocator->get(MotTestRepository::class));
    }
}
