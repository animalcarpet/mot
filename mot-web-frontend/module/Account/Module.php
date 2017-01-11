<?php

namespace Account;

use Account\Service\ClaimAccountService;
use Account\Service\PasswordResetService;
use Account\Service\SecurityQuestionService;
use DvsaCommon\Obfuscate\Factory\ParamObfuscatorFactory;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

/**
 * Class Module
 */
class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                ClaimAccountService::class     => \Account\Factory\Service\ClaimAccountServiceFactory::class,
                PasswordResetService::class    => \Account\Factory\Service\PasswordResetServiceFactory::class,
                SecurityQuestionService::class => \Account\Factory\Service\SecurityQuestionServiceFactory::class,
                ParamObfuscatorFactory::class => ParamObfuscatorFactory::class
            ]
        ];
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }
}
