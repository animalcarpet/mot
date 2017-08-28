<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\AuditApi\Service\HistoryAuditService;
use DvsaCommon\Database\Transaction;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\PasswordDetail;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\NotificationRepository;
use DvsaEntities\Repository\PasswordDetailRepository;
use DvsaEntities\Repository\PersonRepository;
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

        /** @var NotificationService $notificationService */
        $notificationService = $serviceLocator->get(NotificationService::class);
        /** @var NotificationRepository $notificationRepository*/
        $notificationRepository = $entityManager->getRepository(Notification::class);
        /** @var PersonRepository $personRepository*/
        $personRepository = $entityManager->getRepository(Person::class);
        /** @var PasswordDetailRepository $passwordDetailRepository*/
        $passwordDetailRepository = $entityManager->getRepository(PasswordDetail::class);
        /** @var HistoryAuditService $historyAuditService*/
        $historyAuditService = $serviceLocator->get(HistoryAuditService::class);

        return new PasswordExpiryNotificationService(
            $notificationService,
            $notificationRepository,
            $personRepository,
            $passwordDetailRepository,
            new Transaction($entityManager),
            $historyAuditService
        );
    }
}
