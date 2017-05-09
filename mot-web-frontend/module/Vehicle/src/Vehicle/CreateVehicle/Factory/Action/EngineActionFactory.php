<?php

namespace Vehicle\CreateVehicle\Factory\Action;

use Vehicle\CreateVehicle\Action\EngineAction;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EngineActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $createVehichleStepService = $serviceLocator->get(CreateVehicleStepService::class);

        return new EngineAction($createVehichleStepService);
    }
}
