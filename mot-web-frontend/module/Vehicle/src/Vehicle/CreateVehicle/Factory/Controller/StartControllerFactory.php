<?php

namespace Vehicle\CreateVehicle\Factory\Controller;

use Vehicle\CreateVehicle\Action\StartAction;
use Vehicle\CreateVehicle\Controller\StartController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class StartControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $startAction = $serviceLocator->get(StartAction::class);
        return new StartController($startAction);
    }
}