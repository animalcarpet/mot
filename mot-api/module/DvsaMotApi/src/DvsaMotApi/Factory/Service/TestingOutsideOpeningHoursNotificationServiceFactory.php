<?php

namespace DvsaMotApi\Factory\Service;

use DvsaMotApi\Service\TestingOutsideOpeningHoursNotificationService;
use NotificationApi\Service\NotificationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TestingOutsideOpeningHoursNotificationServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new TestingOutsideOpeningHoursNotificationService(
            $serviceLocator->get(NotificationService::class)
        );
    }
}
