<?php

namespace Dashboard\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dashboard\Data\ApiNotificationResource;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

class ApiNotificationResourceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ApiNotificationResource($serviceLocator->get(HttpRestJsonClient::class));
    }
}
