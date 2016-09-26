<?php

namespace AccountApi;

use AccountApi\Factory\Service\ClaimServiceFactory;
use AccountApi\Factory\Service\Validator\ClaimValidatorFactory;
use AccountApi\Factory\Service\OpenAmIdentityServiceFactory;
use AccountApi\Factory\Service\TokenServiceFactory;
use AccountApi\Factory\Service\SecurityQuestionServiceFactory;
use AccountApi\Factory\Service\Validator\PersonSecurityAnswerValidatorFactory;
use AccountApi\Service\ClaimService;
use AccountApi\Service\SecurityQuestionService;
use AccountApi\Service\TokenService;
use AccountApi\Service\Validator\ClaimValidator;
use AccountApi\Service\OpenAmIdentityService;
use AccountApi\Service\Validator\PersonSecurityAnswerValidator;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

/**
 * Class Module
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
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                ClaimService::class => ClaimServiceFactory::class,
                ClaimValidator::class => ClaimValidatorFactory::class,
                OpenAmIdentityService::class => OpenAmIdentityServiceFactory::class,
                TokenService::class => TokenServiceFactory::class,
                SecurityQuestionService::class => SecurityQuestionServiceFactory::class,
                PersonSecurityAnswerValidator::class => PersonSecurityAnswerValidatorFactory::class
            ]
        ];
    }

    public function getAutoloaderConfig()
    {
    }
}
