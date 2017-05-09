<?php

namespace Vehicle\CreateVehicle\Factory\Controller;

use Vehicle\CreateVehicle\Action\EngineAction;
use Vehicle\CreateVehicle\Controller\EngineController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EngineControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $engineAction = $serviceLocator->get(EngineAction::class);

        return new EngineController($engineAction);
    }
}
