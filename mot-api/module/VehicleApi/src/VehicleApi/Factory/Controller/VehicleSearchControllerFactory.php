<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace VehicleApi\Factory\Controller;

use Zend\ServiceManager\FactoryInterface;
use VehicleApi\Service\VehicleSearchService;
use Zend\ServiceManager\ServiceLocatorInterface;
use VehicleApi\Controller\VehicleSearchController;
use DvsaEntities\DqlBuilder\SearchParam\VehicleSearchParam;

/**
 * Create instance of VehicleSearchController.
 *
 * Class VehicleSearchControllerFactory
 */
class VehicleSearchControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        $vehicleSearchService = $sl->get(VehicleSearchService::class);
        $vehicleSearchParam = $sl->get(VehicleSearchParam::class);

        $controller = new VehicleSearchController($vehicleSearchService, $vehicleSearchParam);

        return $controller;
    }
}
