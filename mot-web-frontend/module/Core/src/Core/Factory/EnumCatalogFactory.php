<?php

namespace Core\Factory;

use Application\Service\CatalogService;
use Core\Catalog\EnumCatalog;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EnumCatalogFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var CatalogService $catalog */
        $catalog = $serviceLocator->get('CatalogService');

        return new EnumCatalog($catalog);
    }
}
