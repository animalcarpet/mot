<?php

namespace SiteApi\Factory\Controller;

use SiteApi\Controller\MotTestInProgressController;
use SiteApi\Controller\SiteController;
use SiteApi\Service\MotTestInProgressService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class MotTestInProgressControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return SiteController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new MotTestInProgressController(
            $serviceLocator->get(MotTestInProgressService::class)
        );
    }
}
