<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\CpmsNotification;

class CpmsNotificationRepository extends AbstractMutableRepository
{
    /**
     * @param $notificationId
     *
     * @return null|CpmsNotification
     */
    public function findByNotificationId($notificationId)
    {
        return $this->findOneBy(
            [
                'notificationId' => $notificationId,
            ]
        );
    }
}
