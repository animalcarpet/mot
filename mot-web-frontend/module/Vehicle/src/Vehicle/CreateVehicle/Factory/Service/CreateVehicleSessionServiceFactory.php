<?php

namespace Vehicle\CreateVehicle\Factory\Service;

use DvsaClient\MapperFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Vehicle\CreateVehicle\Service\CreateVehicleSessionService;
use Zend\Session\Container;

class CreateVehicleSessionServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sessionContainer =  new Container(CreateVehicleSessionService::UNIQUE_KEY);
        $mapperFactory = $serviceLocator->get(MapperFactory::class);

        return new CreateVehicleSessionService(
            $sessionContainer,
            $mapperFactory
        );
    }
}