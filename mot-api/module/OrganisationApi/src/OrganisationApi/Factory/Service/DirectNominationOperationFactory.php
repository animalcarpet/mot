<?php

namespace OrganisationApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use OrganisationApi\Model\NominationVerifier;
use OrganisationApi\Model\Operation\DirectNominationOperation;
use OrganisationApi\Service\OrganisationNominationNotificationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaEventApi\Service\EventService;
use DvsaCommon\Date\DateTimeHolder;
use NotificationApi\Service\Helper\OrganisationNominationEventHelper;

/**
 * Class DirectNominationOperationFactory
 * @package OrganisationApi\Factory\Service
 */
class DirectNominationOperationFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new DirectNominationOperation(
            $serviceLocator->get(EntityManager::class),
            $serviceLocator->get(NominationVerifier::class),
            $serviceLocator->get(OrganisationNominationNotificationService::class),
            $serviceLocator->get(EventService::class),
            new DateTimeHolder(),
            $serviceLocator->get(OrganisationNominationEventHelper::class)
        );
    }
}
