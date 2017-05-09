<?php

namespace Dashboard\Factory\Service;

use Core\Catalog\EnumCatalog;
use Dashboard\Service\PersonTradeRoleSorterService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PersonTradeRoleSorterServiceFactory implements FactoryInterface
{
    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PersonTradeRoleSorterService($serviceLocator->get(EnumCatalog::class));
    }
}
