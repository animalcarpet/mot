<?php

namespace Dashboard\ViewModel;

use Countable;
use Iterator;
use Zend\Mvc\Controller\Plugin\Url;

class NotificationsViewModel implements Countable, Iterator
{
    /** @var NotificationViewModel[] $notificationViewModels */
    private $notificationViewModels;

    /** @var int $currentPosition */
    private $currentPosition = 0;

    /**
     * NotificationsViewModel constructor.
     *
     * @param array $notifications
     */
    public function __construct(array $notifications)
    {
        $this->notificationViewModels = $notifications;
    }

    /**
     * @param array $notifications
     * @param Url   $url
     *
     * @return NotificationsViewModel
     */
    public static function fromNotifications(array $notifications, Url $url)
    {
        $notificationViewModels = [];

        foreach ($notifications as $notification) {
            $notificationViewModels[] = NotificationViewModel::fromNotification($notification, $url);
        }

        return new NotificationsViewModel($notificationViewModels);
    }

    /**
     * @return int
     */
    public function countUnread()
    {
        $count = 0;
        foreach ($this->notificationViewModels as $notificationViewModel) {
            if ($notificationViewModel->isUnread()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @return bool
     */
    public function notEmpty()
    {
        return $this->count() > 0;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->notificationViewModels);
    }

    /**
     * @return NotificationViewModel
     */
    public function current()
    {
        return $this->notificationViewModels[$this->currentPosition];
    }

    public function next()
    {
        $this->currentPosition++;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->currentPosition;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return array_key_exists($this->currentPosition, $this->notificationViewModels);
    }

    public function rewind()
    {
        $this->currentPosition = 0;
    }
}
