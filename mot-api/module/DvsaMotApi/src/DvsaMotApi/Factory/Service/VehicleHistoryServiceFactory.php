<?php

namespace DvsaMotApi\Factory\Service;

use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaMotApi\Service\VehicleHistoryService;
use Zend\ServiceManager\FactoryInterface;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class VehicleHistoryServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new VehicleHistoryService(
            $serviceLocator->get(PersonRepository::class),
            $serviceLocator->get(MotTestRepository::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('ConfigurationRepository')
        );
    }
}
