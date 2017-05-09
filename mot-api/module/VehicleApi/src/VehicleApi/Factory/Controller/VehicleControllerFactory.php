<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace VehicleApi\Factory\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use VehicleApi\Service\VehicleService;
use VehicleApi\Service\VehicleSearchService;
use VehicleApi\Controller\VehicleController;

/**
 * Create instance of VehicleController.
 *
 * Class VehicleControllerFactory
 */
class VehicleControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        $vehicleService = $sl->get(VehicleService::class);
        $vehicleSearchService = $sl->get(VehicleSearchService::class);
        $controller = new VehicleController($vehicleService, $vehicleSearchService);

        return $controller;
    }
}
