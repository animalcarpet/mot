<?php

namespace Application\Factory\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Service\CatalogService;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

class CatalogServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new CatalogService(
            $serviceLocator->get('ApplicationWideCache'),
            $serviceLocator->get(HttpRestJsonClient::class)
        );
    }
}
