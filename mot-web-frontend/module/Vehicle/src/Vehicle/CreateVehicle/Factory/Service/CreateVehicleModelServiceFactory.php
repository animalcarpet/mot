<?php

namespace Vehicle\CreateVehicle\Factory\Service;

use Vehicle\CreateVehicle\Service\CreateVehicleModelService;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client;

class CreateVehicleModelServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $createVehicleStepService = $serviceLocator->get(CreateVehicleStepService::class);
        $client = $serviceLocator->get(Client::class);

        return new CreateVehicleModelService(
            $createVehicleStepService,
            $client
        );
    }

}