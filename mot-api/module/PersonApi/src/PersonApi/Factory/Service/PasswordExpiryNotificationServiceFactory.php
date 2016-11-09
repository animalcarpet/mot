<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Database\Transaction;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\PasswordDetail;
use DvsaEntities\Entity\Person;
use DvsaFeature\FeatureToggles;
use NotificationApi\Service\NotificationService;
use PersonApi\Service\PasswordExpiryNotificationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for PasswordExpiryNotificationService.
 */
class PasswordExpiryNotificationServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return PasswordExpiryNotificationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new PasswordExpiryNotificationService(
            $serviceLocator->get(NotificationService::class),
            $entityManager->getRepository(Notification::class),
            $entityManager->getRepository(Person::class),
            $entityManager->getRepository(PasswordDetail::class),
            new Transaction($entityManager)
        );
    }
}
