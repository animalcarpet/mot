<?php

namespace PersonApi\Factory\Helper;

use NotificationApi\Service\NotificationService;
use PersonApi\Helper\PersonDetailsChangeNotificationHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PersonDetailsChangeNotificationHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var NotificationService $notificationService */
        $notificationService = $serviceLocator->get(NotificationService::class);

        return new PersonDetailsChangeNotificationHelper($notificationService);
    }
}
