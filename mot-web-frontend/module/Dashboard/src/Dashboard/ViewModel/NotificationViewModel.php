<?php

namespace Dashboard\ViewModel;

use Dashboard\Controller\NotificationController;
use Dashboard\Model\Notification;
use DvsaCommon\Date\DateTimeDisplayFormat;
use Zend\Mvc\Controller\Plugin\Url;

class NotificationViewModel
{
    /**
     * @var LinkViewModel $linkViewModel
     */
    private $linkViewModel;

    /**
     * @var string $createdOn
     */
    private $createdOn;

    /**
     * @var bool $isUnread
     */
    private $isUnread;

    /**
     * @var bool $isActionRequired
     */
    private $isActionRequired;

    /**
     * NotificationViewModel constructor.
     *
     * @param LinkViewModel $linkViewModel
     * @param string        $createdOn
     * @param bool          $isUnread
     * @param bool          $isActionRequired
     */
    public function __construct(LinkViewModel $linkViewModel, $createdOn, $isUnread = false, $isActionRequired = false)
    {
        $this->linkViewModel = $linkViewModel;
        $this->createdOn = $createdOn;
        $this->isUnread = $isUnread;
        $this->isActionRequired = $isActionRequired;
    }

    /**
     * @param Notification $notification
     * @param Url          $url
     *
     * @return NotificationViewModel
     */
    public static function fromNotification(Notification $notification, Url $url)
    {
        $linkViewModel = new LinkViewModel(
            $notification->getSubject(),
            $url->fromRoute(
                NotificationController::ROUTE_NOTIFICATION,
                ['notificationId' => $notification->getId()],
                ["query" => ["backTo" => NotificationController::BACK_TO_HOME_PARAM]]
            )
        );

        return new NotificationViewModel(
            $linkViewModel,
            DateTimeDisplayFormat::textDateTime($notification->getCreatedOn()),
            $notification->getReadOn() === null,
            $notification->isActionRequired()
        );
    }

    /**
     * Return appropriate CSS class for current notification
     *
     * @return string
     */
    public function getCssClass()
    {
        $cssClass = $this->isUnread ? 'is-unread' : 'is-read';

        if ($this->isActionRequired) {
            $cssClass .= ' is-nomination';
        }

        return $cssClass;
    }

    /**
     * @return LinkViewModel
     */
    public function getLinkViewModel()
    {
        return $this->linkViewModel;
    }

    /**
     * @return LinkViewModel
     */
    public function getLink()
    {
        return $this->getLinkViewModel();
    }

    /**
     * @return string
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @return bool
     */
    public function isUnread()
    {
        return $this->isUnread;
    }
}
