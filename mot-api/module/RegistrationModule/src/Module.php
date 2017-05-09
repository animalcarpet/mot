<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * RegistrationModule module handles user registrations.
 */
class Module implements
    ConfigProviderInterface,
    ControllerProviderInterface,
    ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $moduleConfig = include __DIR__.'/../config/module.config.php';
        $routesConfig = include __DIR__.'/../config/routes.config.php';

        return array_merge($moduleConfig, $routesConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceConfig()
    {
        $servicesConfig = include __DIR__.'/../config/services.config.php';

        return $servicesConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerConfig()
    {
        $controllersConfig = include __DIR__.'/../config/controllers.config.php';

        return $controllersConfig;
    }
}
