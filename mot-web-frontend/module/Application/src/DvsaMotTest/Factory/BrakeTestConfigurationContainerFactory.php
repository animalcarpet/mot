<?php

namespace DvsaMotTest\Factory;

use DvsaMotTest\Helper\BrakeTestConfigurationContainerHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container as SessionContainer;

/**
 * Class BrakeTestConfigurationContainerFactory.
 */
class BrakeTestConfigurationContainerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $container = new SessionContainer();

        return new BrakeTestConfigurationContainerHelper($container);
    }
}
