<?php

namespace OrganisationApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use OrganisationApi\Model\NominationVerifier;
use OrganisationApi\Model\Operation\ConditionalNominationOperation;
use OrganisationApi\Service\OrganisationNominationNotificationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use NotificationApi\Service\NotificationService;

/**
 * Class NominateByRequestOperationFactory
 * @package OrganisationApi\Factory\Service
 */
class NominateByRequestOperationFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ConditionalNominationOperation(
            $serviceLocator->get(EntityManager::class),
            $serviceLocator->get(NominationVerifier::class),
            $serviceLocator->get(OrganisationNominationNotificationService::class)
        );
    }
}
