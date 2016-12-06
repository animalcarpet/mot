<?php


namespace Vehicle\CreateVehicle\Factory\Controller;

use Vehicle\CreateVehicle\Action\ModelAction;
use Vehicle\CreateVehicle\Controller\ModelController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ModelControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        $makeAction = $serviceLocator->get(ModelAction::class);

        return new ModelController($makeAction);
    }
}