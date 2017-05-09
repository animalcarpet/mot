<?php

namespace DvsaMotApi;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Zend module containing the main factory for MOT API services.
 */
class Module implements
    ConfigProviderInterface,
    ServiceProviderInterface
{
    public static $em;

    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return include __DIR__.'/config/services.config.php';
    }
}
