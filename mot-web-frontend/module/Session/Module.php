<?php

namespace Session;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Class Module.
 *
 * @codeCoverageIgnore This class only returns config: no point in testing
 */
class Module implements AutoloaderProviderInterface, ServiceProviderInterface, ConfigProviderInterface
{
    public function getAutoloaderConfig()
    {
    }

    public function getServiceConfig()
    {
        return include __DIR__.'/config/services.config.php';
    }
    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }
}
