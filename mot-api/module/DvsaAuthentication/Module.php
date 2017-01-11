<?php

namespace DvsaAuthentication;

use DvsaAuthentication\Authentication\Listener\AuthenticationListenerFactory;
use Zend\Log\Logger;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\View\Model\JsonModel;

/**
 * Class Module
 *
 * @package DvsaAuthentication
 */
class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            \Zend\Loader\ClassMapAutoloader::class => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            \Zend\Loader\StandardAutoloader::class => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $sm = $app->getServiceManager();

        $listener = $sm->get(AuthenticationListenerFactory::class);
        $app->getEventManager()->attach(MvcEvent::EVENT_ROUTE, $listener, -1);
    }
}
