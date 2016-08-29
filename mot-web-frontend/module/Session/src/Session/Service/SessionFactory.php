<?php

namespace Session\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\Session\Container;

/**
 * Class SessionFactory.
 */
class SessionFactory implements AbstractFactoryInterface
{
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $config = $serviceLocator->get('Config');
        if (isset($config['session_namespace_prefixes'])) {
            foreach ($config['session_namespace_prefixes'] as $prefix) {
                if (stripos($requestedName, $prefix) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $manager = $serviceLocator->get('Zend\Session\SessionManager');

        return new Container($requestedName, $manager);
    }
}
