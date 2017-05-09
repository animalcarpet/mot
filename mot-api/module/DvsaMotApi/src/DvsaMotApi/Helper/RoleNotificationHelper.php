<?php

namespace DvsaMotApi\Helper;

use NotificationApi\Service\NotificationService;
use NotificationApi\Dto\Notification;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonSystemRole;

class RoleNotificationHelper
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @param Person           $person
     * @param PersonSystemRole $role
     *
     * @return int
     */
    public function sendAssignRoleNotification(Person $person, PersonSystemRole $role)
    {
        return $this->send($person, $role, Notification::TEMPLATE_DVSA_ASSIGN_ROLE);
    }

    /**
     * @param Person           $person
     * @param PersonSystemRole $role
     *
     * @return int
     */
    public function sendRemoveRoleNotification(Person $person, PersonSystemRole $role)
    {
        return $this->send($person, $role, Notification::TEMPLATE_DVSA_REMOVE_ROLE);
    }

    /**
     * @param Person           $person
     * @param PersonSystemRole $role
     * @param int              $template
     *
     * @return int
     */
    private function send(Person $person, PersonSystemRole $role, $template)
    {
        $data = (new Notification())
            ->setRecipient($person->getId())
            ->setTemplate($template)
            ->addField('role', $role->getFullName())
            ->toArray();

        return $this->notificationService->add($data);
    }
}
