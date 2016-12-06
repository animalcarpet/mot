<?php

namespace Vehicle\CreateVehicle\Factory\Service;

use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Vehicle\CreateVehicle\Service\CreateVehicleSessionService;
use DvsaCommon\HttpRestJson\Client;

class CreateVehicleStepServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CreateVehicleStepService(
            $serviceLocator->get(CreateVehicleSessionService::class),
            $serviceLocator->get('CatalogService'),
            $serviceLocator->get(Client::class)
        );
    }
}