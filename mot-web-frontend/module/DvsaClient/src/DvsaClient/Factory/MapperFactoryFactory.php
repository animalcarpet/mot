<?php

namespace DvsaClient\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaClient\MapperFactory as Factory;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

class MapperFactoryFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new Factory($serviceLocator->get(HttpRestJsonClient::class));
    }
}
