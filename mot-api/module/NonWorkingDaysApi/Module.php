<?php

namespace NonWorkingDaysApi;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Class Module.
 */
class Module implements AutoloaderProviderInterface, ServiceProviderInterface
{
    public function getAutoloaderConfig()
    {
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'NonWorkingDaysProvider' => \NonWorkingDaysApi\Factory\NonWorkingDaysProviderFactory::class,
                'NonWorkingDaysLookupManager' => \NonWorkingDaysApi\Factory\NonWorkingDaysLookupManagerFactory::class,
                'NonWorkingDaysHelper' => \NonWorkingDaysApi\Factory\NonWorkingDaysHelperFactory::class,
            ],
        ];
    }
}
