<?php

namespace TestSupport\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TestSupport\Helper\TestSupportRestClientHelper;
use TestSupport\Helper\TestSupportAccessTokenManager;
use DvsaCommon\HttpRestJson\Client as JsonClient;

class TestSupportRestClientFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $restClientHelper = new TestSupportRestClientHelper(
            $serviceLocator->get(JsonClient::class),
            $serviceLocator->get(TestSupportAccessTokenManager::class)
        );

        return $restClientHelper;
    }
}
