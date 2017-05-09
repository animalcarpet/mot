<?php

namespace Dashboard\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dashboard\Data\ApiDashboardResource;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

class ApiDashboardResourceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ApiDashboardResource($serviceLocator->get(HttpRestJsonClient::class));
    }
}
