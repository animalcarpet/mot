<?php

namespace Event;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Event Module.
 */
class Module implements
    ConfigProviderInterface,
    ServiceProviderInterface,
    ControllerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = array_merge(
            include __DIR__.'/config/routes.config.php',
            include __DIR__.'/config/module.config.php'
        );

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerConfig()
    {
        return include __DIR__.'/config/controllers.config.php';
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceConfig()
    {
        return include __DIR__.'/config/services.config.php';
    }
}
