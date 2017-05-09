<?php

namespace DvsaEntities;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

/**
 * Class Module.
 */
class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    public function getAutoloaderConfig()
    {
    }

    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }
}
