<?php

namespace DvsaEntitiesTest\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\NotificationActionLookup;
use DvsaEntities\Entity\NotificationField;
use DvsaEntities\Entity\NotificationTemplate;
use DvsaEntities\Entity\NotificationTemplateAction;
use DvsaEntities\Entity\Person;

trait NotificationCreatorTrait
{
    /**
     * @param array $fields
     *
     * @return Notification
     */
    private function createNotificationWithFields($fields = [])
    {
        $notification = $this->createNotification();
        $notificationFields = [];

        foreach ($fields as $key => $value) {
            $notificationFields[] = (new NotificationField())->setField($key)->setValue($value);
        }

        $notification->setFields(new ArrayCollection($notificationFields));

        return $notification;
    }

    /**
     * @param array $actionNames
     *
     * @return Notification
     */
    private function createNotificationWithTemplateAction(array $actionNames = null)
    {
        $notification = $this->createNotification();
        if (is_array($actionNames)) {
            foreach ($actionNames as $action) {
                $this->addTemplateActionToNotification($notification, $action);
            }
        } else {
            $this->addTemplateActionToNotification($notification, '');
        }

        return $notification;
    }

    /**
     * @param Notification $notification
     * @param string       $actionName
     *
     * @return Notification
     */
    private function addTemplateActionToNotification(Notification $notification, $actionName)
    {
        $action = new NotificationTemplateAction();
        $actionLookup = new NotificationActionLookup();
        $actionLookup->setAction($actionName);
        $action->setAction($actionLookup);

        $allActions = $notification->getNotificationTemplate()->getActions();

        if (count($allActions) === 0) {
            $allActions = new ArrayCollection([$action]);
        } else {
            $allActions->add($action);
        }

        $notification->getNotificationTemplate()->setActions($allActions);

        return $notification;
    }

    /**
     * @return Notification
     */
    private function createNotification()
    {
        $notification = new Notification();
        $person = new Person();
        $notification->setRecipient($person);
        $template = new NotificationTemplate();
        $notification->setNotificationTemplate($template);

        return $notification;
    }
}
