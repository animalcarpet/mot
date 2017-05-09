<?php

namespace DvsaCommonApi\Factory\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommonApi\Listener\JsonContentTypeFilter;

class JsonContentTypeFilterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new JsonContentTypeFilter();
    }
}
