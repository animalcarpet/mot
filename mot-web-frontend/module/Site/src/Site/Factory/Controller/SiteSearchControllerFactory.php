<?php

namespace Site\Factory\Controller;

use DvsaClient\MapperFactory;
use Site\Controller\SiteSearchController;
use Site\Service\SiteSearchService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SiteSearchControllerFactory.
 */
class SiteSearchControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return SiteSearchController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        $appServiceLocator = $controllerManager->getServiceLocator();

        /**
         * @var MapperFactory
         */
        $mapper = $appServiceLocator->get(MapperFactory::class);
        /**
         * @var SiteSearchService
         */
        $service = $appServiceLocator->get(SiteSearchService::class);

        return new SiteSearchController($mapper, $service);
    }
}
