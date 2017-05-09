<?php

namespace DataCatalogApi;

use DataCatalogApi\Service\DataCatalogService;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Class Module.
 */
class Module implements
AutoloaderProviderInterface,
                        ConfigProviderInterface,
                        ServiceProviderInterface
{
    public function getAutoloaderConfig()
    {
    }

    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                DataCatalogService::class => \DataCatalogApi\Factory\Service\DataCatalogServiceFactory::class,
                'VehicleCatalogService' => \DataCatalogApi\Factory\Service\VehicleCatalogServiceFactory::class,
            ],
        ];
    }
}
