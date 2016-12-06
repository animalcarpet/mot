<?php

namespace Vehicle\CreateVehicle\Factory\Controller;

use Vehicle\CreateVehicle\Action\RegistrationAndVinAction;
use Vehicle\CreateVehicle\Controller\RegistrationAndVinController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RegistrationAndVinControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $action = $serviceLocator->get(RegistrationAndVinAction::class);

        return new RegistrationAndVinController(
            $action
        );
    }
}