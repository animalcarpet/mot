<?php

namespace SiteApi\Factory\Controller;

use SiteApi\Controller\SiteDetailsController;
use SiteApi\Controller\SiteTestingFacilitiesController;
use SiteApi\Service\SiteDetailsService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SiteControllerFactory.
 */
class SiteDetailsControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return SiteTestingFacilitiesController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var SiteDetailsService $siteDetailsService */
        $siteDetailsService = $serviceLocator->get(SiteDetailsService::class);

        return new SiteDetailsController(
            $siteDetailsService
        );
    }
}
