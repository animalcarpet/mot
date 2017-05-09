<?php

namespace DvsaMotTest\Factory\Service;

use DvsaClient\MapperFactory;
use DvsaMotTest\Service\StartTestSessionService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

class StartTestSessionServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sessionContainer = new Container(StartTestSessionService::UNIQUE_KEY);
        $mapperFactory = $serviceLocator->get(MapperFactory::class);

        return new StartTestSessionService(
            $sessionContainer,
            $mapperFactory
        );
    }
}
