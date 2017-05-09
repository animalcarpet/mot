<?php

namespace Vehicle\Factory\Service;

use Vehicle\Service\VehicleCatalogService;
use Zend\ServiceManager\FactoryInterface;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use Zend\ServiceManager\ServiceLocatorInterface;

class VehicleCatalogServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return VehicleCatalogService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new VehicleCatalogService(
            $serviceLocator->get(HttpRestJsonClient::class)
        );
    }
}
