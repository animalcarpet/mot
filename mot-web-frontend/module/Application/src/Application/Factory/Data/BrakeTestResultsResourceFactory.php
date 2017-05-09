<?php

namespace Application\Factory\Data;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaMotTest\Data\BrakeTestResultsResource;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

class BrakeTestResultsResourceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new BrakeTestResultsResource($serviceLocator->get(HttpRestJsonClient::class));
    }
}
