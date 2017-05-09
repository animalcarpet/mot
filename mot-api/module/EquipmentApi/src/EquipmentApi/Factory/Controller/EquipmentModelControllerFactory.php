<?php

namespace EquipmentApi\Factory\Controller;

use EquipmentApi\Controller\EquipmentModelController;
use EquipmentApi\Service\EquipmentModelService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class EquipmentModelControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var EquipmentModelService $service */
        $service = $serviceLocator->get(EquipmentModelService::class);

        return new EquipmentModelController($service);
    }
}
