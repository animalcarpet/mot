<?php

namespace Vehicle\CreateVehicle\Factory\Controller;

use Vehicle\CreateVehicle\Action\CountryOfRegistrationAction;
use Vehicle\CreateVehicle\Controller\CountryOfRegistrationController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CountryOfRegistrationControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $action = $serviceLocator->get(CountryOfRegistrationAction::class);

        return new CountryOfRegistrationController(
            $action
        );
    }
}