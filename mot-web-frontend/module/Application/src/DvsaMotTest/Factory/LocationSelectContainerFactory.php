<?php

namespace DvsaMotTest\Factory;

use DvsaMotTest\Helper\LocationSelectContainerHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container as SessionContainer;

/**
 * Class LocationSelectContainerFactory.
 */
class LocationSelectContainerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $container = new SessionContainer();

        return new LocationSelectContainerHelper($container);
    }
}
