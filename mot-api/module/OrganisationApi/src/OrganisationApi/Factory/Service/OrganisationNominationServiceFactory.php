<?php

namespace OrganisationApi\Factory\Service;

use NotificationApi\Service\NotificationService;
use OrganisationApi\Service\OrganisationNominationNotificationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class OrganisationNominationServiceFactory.
 */
class OrganisationNominationServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new OrganisationNominationNotificationService(
            $serviceLocator->get(NotificationService::class)
        );
    }
}
