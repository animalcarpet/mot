<?php

namespace PersonApi\Helper;

use NotificationApi\Service\NotificationService;
use NotificationApi\Dto\Notification;
use DvsaEntities\Entity\Person;

class PersonDetailsChangeNotificationHelper
{
    /**
     * @var NotificationService
     */
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @param Person $person
     *
     * @return int
     */
    public function sendChangedPersonalDetailsNotification(Person $person)
    {
        return $this->send($person, Notification::TEMPLATE_PERSONAL_DETAILS_CHANGED);
    }

    /**
     * @param Person $person
     * @param int    $template
     *
     * @return int
     */
    private function send(Person $person, $template)
    {
        $data = (new Notification())
            ->setRecipient($person->getId())
            ->setTemplate($template)
            ->toArray();

        return $this->notificationService->add($data);
    }
}
