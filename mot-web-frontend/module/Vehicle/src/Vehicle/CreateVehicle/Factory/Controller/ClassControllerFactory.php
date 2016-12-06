<?php


namespace Vehicle\CreateVehicle\Factory\Controller;

use Vehicle\CreateVehicle\Action\ClassAction;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Vehicle\CreateVehicle\Controller\ClassController;

class ClassControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        $action = $serviceLocator->get(ClassAction::class);

        return new ClassController(
            $action
        );
    }
}