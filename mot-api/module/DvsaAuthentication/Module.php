<?php

namespace DvsaAuthentication;

use DvsaAuthentication\Authentication\Listener\AuthenticationListenerFactory;
use Zend\Mvc\MvcEvent;

/**
 * Class Module.
 */
class Module
{
    public function getAutoloaderConfig()
    {
    }

    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $sm = $app->getServiceManager();

        $listener = $sm->get(AuthenticationListenerFactory::class);
        $app->getEventManager()->attach(MvcEvent::EVENT_ROUTE, $listener, -1);
    }
}
