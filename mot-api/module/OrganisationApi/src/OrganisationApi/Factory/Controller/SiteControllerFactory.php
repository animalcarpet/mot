<?php

namespace OrganisationApi\Factory\Controller;

use OrganisationApi\Controller\SiteController;
use OrganisationApi\Service\SiteService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class SiteControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new SiteController(
            $serviceLocator->get(SiteService::class)
        );
    }
}
