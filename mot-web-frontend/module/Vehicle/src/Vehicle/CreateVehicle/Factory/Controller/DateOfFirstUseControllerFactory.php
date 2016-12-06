<?php

namespace Vehicle\CreateVehicle\Factory\Controller;

use Vehicle\CreateVehicle\Action\DateOfFirstUseAction;
use Vehicle\CreateVehicle\Controller\DateOfFirstUseController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DateOfFirstUseControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $dateOfFirstUseAction = $serviceLocator->get(DateOfFirstUseAction::class);
        return new DateOfFirstUseController($dateOfFirstUseAction);
    }
}