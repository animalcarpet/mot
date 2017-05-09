<?php

namespace TestSupport\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\CpmsNotification;
use DvsaEntities\Repository\CpmsNotificationRepository;

class PaymentNotificationsAuditService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param $notification
     *
     * @return CpmsNotification
     */
    public function fetchNotificationData($notification)
    {
        $cpmsNotificationRep = new CpmsNotificationRepository($this->entityManager,
            $this->entityManager->getClassMetadata(CpmsNotification::class));

        return $cpmsNotificationRep->findByNotificationId($notification);
    }
}
