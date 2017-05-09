<?php

namespace TestSupport\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use TestSupport\Helper\NotificationsHelper;
use TestSupport\Helper\TestSupportRestClientHelper;

class NotificationsHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new NotificationsHelper(
            $serviceLocator->get(TestSupportRestClientHelper::class)
        );
    }
}
