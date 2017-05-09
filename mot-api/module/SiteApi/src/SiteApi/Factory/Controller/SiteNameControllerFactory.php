<?php

namespace SiteApi\Factory\Controller;

use SiteApi\Controller\SiteNameController;
use SiteApi\Service\SiteService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class SiteNameControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new SiteNameController(
            $serviceLocator->get(SiteService::class)
        );
    }
}
