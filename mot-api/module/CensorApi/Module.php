<?php
namespace CensorApi;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;

/**
 * Class Module
 *
 * @package CensorApi
 */
class Module implements AutoloaderProviderInterface,
 ConfigProviderInterface,
 ServiceProviderInterface
{
    public function getAutoloaderConfig()
    {
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                'CensorService'          => \CensorApi\Factory\Service\CensorServiceFactory::class,
            ]
        ];
    }
}
