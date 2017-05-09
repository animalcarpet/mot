<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\DashboardController;
use PersonApi\Service\DashboardService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class DashboardControllerFactory.
 *
 * Generates the DashboardController, injecting dependencies
 */
class DashboardControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return DashboardController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var DashboardService $dashboardService */
        $dashboardService = $serviceLocator->get(DashboardService::class);

        return new DashboardController($dashboardService);
    }
}
