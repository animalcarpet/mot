<?php

namespace DvsaCommonApi;

use DvsaCommon\Obfuscate\ParamEncrypter;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use DvsaCommonApi\Factory\Filter\XssFilterFactory;
use DvsaCommonApi\Factory\Listener\ClaimAccountListenerFactory;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Listener\ClaimAccountListener;
use DvsaCommonApi\Service\EntityHelperService;
use DvsaMotApi\Factory\Assertion\ApiPerformMotTestAssertionFactory;
use DvsaMotApi\Factory\Assertion\ApiReadMotTestAssertionFactory;
use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;

/**
 * DvsaCommonApi Module.
 */
class Module implements ConfigProviderInterface, ServiceProviderInterface, BootstrapListenerInterface
{
    /**
     * @param EventInterface|MvcEvent $e
     *
     * @return array|void
     */
    public function onBootstrap(EventInterface $e)
    {
        if ($e->getRequest() instanceof ConsoleRequest) {
            return;
        }

        $target = $e->getTarget();
        /** @var ServiceManager $sm */
        $sm = $target->getServiceManager();
        //TODO: Eager logger initialization fixes ClassLoader function nesting error (currently capped to 100)
        $sm->get('Application\Logger');
        $target->getEventManager()->attach($sm->get('ErrorHandlingListener'));
        $target->getEventManager()->attach($sm->get('UnauthorizedStrategy'));
        $target->getEventManager()->attach($sm->get('JsonContentTypeFilter'));
        $target->getEventManager()->attach($sm->get(ClaimAccountListener::class));
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                'ErrorHandlingListener' => \DvsaCommonApi\Factory\Listener\ErrorHandlingListenerFactory::class,
                EntityHelperService::class => \DvsaCommonApi\Factory\Service\EntityHelperServiceFactory::class,
                'Application\SqlLogger' => \DvsaCommonApi\Factory\SqlLoggerFactory::class,
                'UnauthorizedStrategy' => \DvsaCommonApi\Factory\Listener\UnauthorizedStrategyFactory::class,
                'JsonContentTypeFilter' => \DvsaCommonApi\Factory\Listener\JsonContentTypeFilterFactory::class,
                'Hydrator' => \DvsaCommonApi\Factory\HydratorFactory::class,
                ApiPerformMotTestAssertion::class => ApiPerformMotTestAssertionFactory::class,
                ReadMotTestAssertion::class => ApiReadMotTestAssertionFactory::class,
                ClaimAccountListener::class => ClaimAccountListenerFactory::class,
                // @TODO after mot-common-web-module is part of composer remove the below lines as the module will already have these services registered.
                ParamEncrypter::class => \DvsaCommon\Obfuscate\Factory\ParamEncrypterFactory::class,
                ParamObfuscator::class => \DvsaCommon\Obfuscate\Factory\ParamObfuscatorFactory::class,
                XssFilter::class => XssFilterFactory::class,
            ],
        ];
    }
}
