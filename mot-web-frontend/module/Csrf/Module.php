<?php

namespace Csrf;

use Zend\EventManager\EventInterface;
use Zend\Http\Request as HttpRequest;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\Mvc\MvcEvent;

/**
 * Csrf Module.
 */
class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface,
    ServiceProviderInterface,
    ViewHelperProviderInterface
{
    const CSRF_VALIDATING_LISTENER_PRIORITY = 999;

    /**
     * @param \Zend\EventManager\EventInterface $e
     */
    public function onBootstrap(EventInterface $e)
    {
        if (!($e instanceof MvcEvent) || !($e->getRequest() instanceof HttpRequest)) {
            return;
        }

        $eventManager = $e->getApplication()->getEventManager();
        $sm = $e->getApplication()->getServiceManager();
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, [$sm->get('CsrfValidatingListener'), 'validate'],
            self::CSRF_VALIDATING_LISTENER_PRIORITY);
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getViewHelperConfig()
    {
        return include __DIR__.'/config/viewhelper.config.php';
    }

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return include __DIR__.'/config/services.config.php';
    }
}
