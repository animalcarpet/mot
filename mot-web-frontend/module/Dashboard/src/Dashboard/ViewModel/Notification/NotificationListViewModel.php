<?php

namespace Dashboard\ViewModel\Notification;

use Dashboard\Controller\NotificationController;
use Dashboard\Model\Notification;
use Zend\View\Helper\Url;

class NotificationListViewModel
{
    private $activeTabTemplate = '<a href="%s" id="%s" class="c-tab-list__tab-link">%s</a>';
    private $inactiveTabTemplate = '<span class="c-tab-list__tab-link c-tab-list__tab-link--active">%s</span>';

    /** @var  Notification[] */
    private $notifications;
    /** @var  int */
    private $unreadCount = 0;
    /** @var  bool */
    private $isArchiveView = false;

    private $urlPlugin;

    public function __construct(Url $urlPlugin)
    {
        $this->urlPlugin = $urlPlugin;
    }

    /**
     * @return \Dashboard\Model\Notification[]
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param \Dashboard\Model\Notification[] $notifications
     * @return NotificationListViewModel
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
        return $this;
    }

    /**
     * @return int
     */
    public function getUnreadCount()
    {
        return $this->unreadCount;
    }

    /**
     * @param int $unreadCount
     * @return NotificationListViewModel
     */
    public function setUnreadCount($unreadCount)
    {
        $this->unreadCount = $unreadCount;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isArchiveView()
    {
        return $this->isArchiveView;
    }

    /**
     * @param boolean $isArchiveView
     * @return NotificationListViewModel
     */
    public function setIsArchiveView($isArchiveView)
    {
        $this->isArchiveView = $isArchiveView;
        return $this;
    }

    public function notificationsAreEmpty()
    {
        return count($this->notifications) == 0;
    }

    public function getEmptyNotificationsMessage()
    {
        return $this->isArchiveView()
            ? "You don't have any archived notifications"
            : "You don't have any new notifications";
    }

    public function getInboxTab()
    {
        $inboxLink = $this->getUrlForRoute(NotificationController::ROUTE_NOTIFICATION_LIST);
        $inboxText = "Inbox ({$this->getUnreadCount()})";
        $inboxTabId = "inbox-tab";

        return !$this->isArchiveView()
            ? sprintf($this->inactiveTabTemplate, $inboxText)
            : sprintf($this->activeTabTemplate, $inboxLink, $inboxTabId, $inboxText);
    }

    public function getArchiveTab()
    {
        $archiveLink = $this->getUrlForRoute(NotificationController::ROUTE_NOTIFICATION_ARCHIVE);
        $archiveText = "Archive";
        $archiveTabId = "archive-tab";

        return $this->isArchiveView()
            ? sprintf($this->inactiveTabTemplate, $archiveText)
            : sprintf($this->activeTabTemplate, $archiveLink, $archiveTabId, $archiveText);
    }

    private function getUrlForRoute($route)
    {
        $url = $this->urlPlugin;

        return $url($route);
    }
}