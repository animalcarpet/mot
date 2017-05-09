<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule;

use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggleViewHelperFactory;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

class Module implements
    ConfigProviderInterface,
    ControllerProviderInterface,
    ServiceProviderInterface,
    ViewHelperProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = array_merge(
            include __DIR__.'/../config/routes.config.php',
            include __DIR__.'/../config/module.config.php'
        );

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerConfig()
    {
        return include __DIR__.'/../config/controllers.config.php';
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceConfig()
    {
        return include __DIR__.'/../config/services.config.php';
    }

    /**
     * {@inheritdoc}
     */
    public function getViewHelperConfig()
    {
        return [
            'factories' => [
                'isTwoFaEnabled' => TwoFaFeatureToggleViewHelperFactory::class,
            ],
        ];
    }
}
