<?php

namespace SiteApi\Factory\Controller;

use SiteApi\Controller\SiteEventController;
use SiteApi\Service\SiteEventService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SiteEventControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return SiteEventController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new SiteEventController(
            $serviceLocator->get(SiteEventService::class)
        );
    }
}
