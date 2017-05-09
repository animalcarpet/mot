<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace Account;

use Account\Factory\Service\ExpiredPasswordServiceFactory;
use Account\Service\ClaimAccountService;
use Account\Service\ExpiredPasswordService;
use Account\Service\PasswordResetService;
use Account\Service\SecurityQuestionService;
use DvsaCommon\Obfuscate\Factory\ParamObfuscatorFactory;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

/**
 * Class Module.
 */
class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                ClaimAccountService::class => \Account\Factory\Service\ClaimAccountServiceFactory::class,
                PasswordResetService::class => \Account\Factory\Service\PasswordResetServiceFactory::class,
                SecurityQuestionService::class => \Account\Factory\Service\SecurityQuestionServiceFactory::class,
                ParamObfuscatorFactory::class => ParamObfuscatorFactory::class,
                ExpiredPasswordService::class => ExpiredPasswordServiceFactory::class,
            ],
        ];
    }

    public function getAutoloaderConfig()
    {
    }
}
