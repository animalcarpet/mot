<?php

namespace OrganisationApi\Factory\Controller;

use OrganisationApi\Controller\SiteLinkController;
use OrganisationApi\Service\SiteLinkService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class SiteLinkControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new SiteLinkController(
            $serviceLocator->get(SiteLinkService::class)
        );
    }
}
