<?php

namespace OrganisationApi\Factory\Service;

use NotificationApi\Service\NotificationService;
use OrganisationApi\Service\OrganisationNominationNotificationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class OrganisationNominationServiceFactory
 * @package OrganisationApi\Factory\Service
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
