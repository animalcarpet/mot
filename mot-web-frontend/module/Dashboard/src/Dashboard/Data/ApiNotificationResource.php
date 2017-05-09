<?php

namespace Dashboard\Data;

use Application\Data\ApiResources;
use DvsaCommon\UrlBuilder\NotificationUrlBuilder;

/**
 * Handles calls to API, paths:
 *      /notification/:notificationId[/action]
 *      /notification/person/:personId[/read].
 */
class ApiNotificationResource extends ApiResources
{
    /**
     * @param int $notificationId
     *
     * @return array
     */
    public function markAsRead($notificationId)
    {
        $path = $this->notificationResource($notificationId)->read()->toString();

        return $this->restUpdate($path, [])['data'];
    }

    /**
     * @param int $notificationId
     *
     * @return array
     */
    public function get($notificationId)
    {
        $path = $this->notificationResource($notificationId)->toString();

        return $this->restGet($path)['data'];
    }

    /**
     * @param int $notificationId
     *
     * @return array
     */
    public function archive($notificationId)
    {
        $path = $this->notificationResource($notificationId)->archive()->toString();

        return $this->restUpdate($path, []);
    }

    /**
     * @param int $personId
     *
     * @return array
     */
    public function getInboxNotifications($personId)
    {
        $path = $this->notificationForPersonResource($personId)->toString();

        return $this->restGet($path)['data'];
    }

    /**
     * @param int $personId
     *
     * @return array
     */
    public function getUnreadCount($personId)
    {
        $path = $this->notificationUnreadCountForPerson($personId)->toString();

        return $this->restGet($path)['data'];
    }
    /**
     * @param int $personId
     *
     * @return array
     */
    public function getArchivedNotifications($personId)
    {
        $path = $this->notificationForPersonResource($personId)->queryParam('archived', 1)->toString();

        return $this->restGet($path)['data'];
    }

    /**
     * Calls notification action (nomination).
     *
     * @param int    $personId
     * @param int    $notificationId
     * @param string $action
     *
     * @return array
     */
    public function notificationAction($personId, $notificationId, $action)
    {
        $path = $this->notificationResource($notificationId)->action()->toString();

        $data = [
            'action' => $action,
        ];

        return $this->restUpdate($path, $data)['data'];
    }

    /**
     * @param int $personId
     *
     * @return NotificationUrlBuilder
     */
    private function notificationUnreadCountForPerson($personId)
    {
        return NotificationUrlBuilder::unreadNotificationsCountForPerson($personId);
    }

    /**
     * @param int $personId
     *
     * @return NotificationUrlBuilder
     */
    private function notificationForPersonResource($personId)
    {
        return NotificationUrlBuilder::notificationForPerson()->routeParam('personId', $personId);
    }

    /**
     * @param int $notification
     *
     * @return NotificationUrlBuilder
     */
    private function notificationResource($notification)
    {
        return NotificationUrlBuilder::notification($notification);
    }
}
