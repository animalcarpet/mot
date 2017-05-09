<?php

namespace Application\Factory\Data;

use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaMotTest\Data\TesterInProgressTestNumberResource;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TesterInProgressTestNumberResourceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new TesterInProgressTestNumberResource($serviceLocator->get(HttpRestJsonClient::class));
    }
}
