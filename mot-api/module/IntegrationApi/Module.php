<?php

namespace IntegrationApi;

use IntegrationApi\DvlaVehicle\Service\DvlaVehicleUpdatedService;
use IntegrationApi\OpenInterface\Repository\OpenInterfaceMotTestRepository as OpenInterfaceMotTestRepository;
use IntegrationApi\OpenInterface\Service\OpenInterfaceMotTestService;
use IntegrationApi\TransportForLondon\Service\TransportForLondonMotTestService;
use IntegrationApi\DvlaInfo\Service\DvlaInfoMotHistoryService;

/**
 * Class Module.
 */
class Module
{
    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                OpenInterfaceMotTestRepository::class => \IntegrationApi\Factory\OpenInterfaceMotTestRepositoryFactory::class,
                OpenInterfaceMotTestService::class => \IntegrationApi\Factory\OpenInterfaceMotTestServiceFactory::class,
                TransportForLondonMotTestService::class => \IntegrationApi\Factory\TransportForLondonMotTestServiceFactory::class,
                DvlaInfoMotHistoryService::class => \IntegrationApi\Factory\DvlaInfoMotHistoryServiceFactory::class,
                DvlaVehicleUpdatedService::class => \IntegrationApi\Factory\Service\DvlaVehicleUpdatedServiceFactory::class,
            ],
        ];
    }
}
