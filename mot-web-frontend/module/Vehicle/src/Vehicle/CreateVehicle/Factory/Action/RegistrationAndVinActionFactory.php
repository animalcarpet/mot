<?php

namespace Vehicle\CreateVehicle\Factory\Action;

use Vehicle\CreateVehicle\Action\RegistrationAndVinAction;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RegistrationAndVinActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new RegistrationAndVinAction(
            $serviceLocator->get(CreateVehicleStepService::class)
        );
    }
}
