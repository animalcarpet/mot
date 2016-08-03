<?php

namespace TestSupport;

use Doctrine\ORM\EntityManager;
use TestSupport\Service\JsonErrorHandlingListener;
use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Basic logic for module
 */
class Module implements ConfigProviderInterface, ServiceProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $application   = $e->getApplication();
        $sm            = $application->getServiceManager();
        $sharedManager = $application->getEventManager()->getSharedManager();

        $sharedManager->attach(
            'Zend\Mvc\Application', MvcEvent::EVENT_DISPATCH_ERROR,
            function ($e) use ($sm) {
                if ($e->getParam('exception')) {
                    $sm->get('ApplicationLog')->crit($e->getParam('exception'));
                }
            }
        );

        /** @var EntityManager $em */
        $em = $sm->get(EntityManager::class);
        $em->getConnection()->exec("SET @app_user_id = (SELECT `id` FROM `person` WHERE `user_reference` = 'Static Data' OR `username` = 'static data')");

        $application->getEventManager()->attach($sm->get(JsonErrorHandlingListener::class));
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return include __DIR__ . '/config/services.config.php';
    }
}
