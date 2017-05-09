<?php

namespace IntegrationApi\Factory\Controller;

use IntegrationApi\DvlaVehicle\Controller\DvlaVehicleUpdatedController;
use IntegrationApi\DvlaVehicle\Service\DvlaVehicleUpdatedService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DvlaVehicleUpdatedFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        $serviceLocator = $controllerManager->getServiceLocator();
        $dvlaVehicleUpdatedService = $serviceLocator->get(DvlaVehicleUpdatedService::class);

        return new DvlaVehicleUpdatedController($dvlaVehicleUpdatedService);
    }
}
