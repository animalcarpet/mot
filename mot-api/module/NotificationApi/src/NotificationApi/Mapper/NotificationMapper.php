<?php

namespace NotificationApi\Mapper;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\NotificationTemplateAction;

/**
 * Class NotificationMapper
 */
class NotificationMapper
{
    /**
     * @param Notification $notification
     *
     * @return array
     */
    public function toArray(Notification $notification = null)
    {
        $result = [];

        if (null === $notification) {
            return $result;
        }

        $fields = [];

        /** @var $object \DvsaEntities\Entity\NotificationField */
        foreach ($notification->getFields() as $object) {
            $fields[$object->getField()] = $object->getValue();
        }

        $result = [
            'id'        => $notification->getId(),
            'recipientId' => $notification->getRecipient(),
            'templateId' => $notification->getNotificationTemplate()->getId(),
            'subject'   => $this->parseTemplate($notification->getNotificationTemplate()->getSubject(), $fields),
            'content'   => $this->parseTemplate($notification->getNotificationTemplate()->getContent(), $fields),
            'readOn'    => $this->extractDate($notification->getReadOn()),
            'createdOn' => $this->extractDate($notification->getCreatedOn()),
            'isArchived' => $notification->getIsArchived(),
            'fields' => $fields
        ];

        /** @var \DvsaEntities\Entity\NotificationAction $notificationAction */
        $notificationAction = $notification->getAction();

        if ($notificationAction) {
            $result['updatedOn'] = $this->extractDate($notificationAction->getCreatedOn());
        } else {
            $result['updatedOn'] = '';
        }

        if ($notification->getNotificationTemplate()->getActions()) {
            $actions = $notification->getNotificationTemplate()->getActions();
            /** @var $action NotificationTemplateAction */
            foreach ($actions as $action) {
                $result['actions'][$action->getAction()->getAction()] = $action->getLabel();
                $result['action'] = null;
            }
        }

        if ($notification->getAction()) {
            $result['action'] = $notification->getAction()->getAction()->getAction();
        }

        return $result;
    }

    private function parseTemplate($content, $fields)
    {
        foreach ($fields as $key => $value) {
            $content = str_replace('${' . $key . '}', $value, $content);
        }

        return $content;
    }

    private function extractDate(\DateTime $date = null)
    {
        if (null === $date) {
            return '';
        }

        return DateTimeApiFormat::dateTime($date);
    }
}
