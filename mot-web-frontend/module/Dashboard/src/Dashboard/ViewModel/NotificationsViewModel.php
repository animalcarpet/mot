<?php

namespace Dashboard\ViewModel;

use Countable;
use Iterator;
use Zend\Mvc\Controller\Plugin\Url;

class NotificationsViewModel implements Countable, Iterator
{
    /** @var NotificationViewModel[] $notificationViewModels */
    private $notificationViewModels;

    /** @var int $totalUnreadCount */
    private $totalUnreadCount;

    /** @var int $currentPosition */
    private $currentPosition = 0;

    /**
     * NotificationsViewModel constructor.
     *
     * @param array $notifications
     * @param int   $totalUnreadCount
     */
    public function __construct(array $notifications, $totalUnreadCount)
    {
        $this->notificationViewModels = $notifications;
        $this->totalUnreadCount = $totalUnreadCount;
    }

    /**
     * @param array $notifications
     * @param int   $totalUnreadCount
     * @param Url   $url
     *
     * @return NotificationsViewModel
     */
    public static function fromNotifications(array $notifications, $totalUnreadCount, Url $url)
    {
        $notificationViewModels = [];

        foreach ($notifications as $notification) {
            $notificationViewModels[] = NotificationViewModel::fromNotification($notification, $url);
        }

        return new self($notificationViewModels, $totalUnreadCount);
    }

    /**
     * @return int
     */
    public function countUnread()
    {
        return $this->totalUnreadCount;
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
        ++$this->currentPosition;
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
