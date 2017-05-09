<?php

namespace NotificationApi\Factory\Service;

use NotificationApi\Service\NotificationService;
use NotificationApi\Service\PositionRemovalNotificationService;
use NotificationApi\Service\UserOrganisationNotificationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserOrganisationNotificationServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return UserOrganisationNotificationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new UserOrganisationNotificationService(
            $serviceLocator->get(NotificationService::class),
            $serviceLocator->get(PositionRemovalNotificationService::class)
        );
    }
}
