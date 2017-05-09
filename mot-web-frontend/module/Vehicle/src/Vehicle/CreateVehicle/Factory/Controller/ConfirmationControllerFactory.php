<?php

namespace Vehicle\CreateVehicle\Factory\Controller;

use Vehicle\CreateVehicle\Action\ConfirmationAction;
use Vehicle\CreateVehicle\Controller\ConfirmationController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConfirmationControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $action = $serviceLocator->get(ConfirmationAction::class);

        return new ConfirmationController($action);
    }
}
