<?php

namespace DataCatalogApi\Factory\Service;

use DataCatalogApi\Service\DataCatalogService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class DataCatalogServiceFactory.
 */
class DataCatalogServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dataCatalogService = new DataCatalogService(
            $serviceLocator->get(EntityManager::class),
            $serviceLocator->get('Hydrator'),
            $serviceLocator->get('DvsaAuthorisationService')
        );

        return $dataCatalogService;
    }
}
