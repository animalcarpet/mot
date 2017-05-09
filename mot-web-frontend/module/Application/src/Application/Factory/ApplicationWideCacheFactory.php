<?php

namespace Application\Factory;

use Zend\Session\Container;
use Zend\Cache\StorageFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ApplicationWideCacheFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return StorageFactory::factory(
            [
                'adapter' => [
                    'name' => 'session',
                    'options' => [
                        'sessionContainer' => new Container('applicationWideCache'),
                    ],
                ],
            ]
        );
    }
}
