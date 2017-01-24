<?php

namespace Dashboard\Action;

use Core\Action\ViewActionResult;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dashboard\Data\ApiNotificationResource;
use Dashboard\Model\Notification;
use Dashboard\ViewModel\Notification\NotificationListViewModel;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\View\Helper\Url;

class NotificationAction implements AutoWireableInterface
{
    const VIEW_TEMPLATE = 'dashboard/notification/inbox.phtml';

    private $notificationResource;
    private $frontendIdentityProvider;
    private $urlPlugin;

    public function __construct(
        Url $url,
        ApiNotificationResource $notificationResource,
        MotFrontendIdentityProviderInterface $frontendIdentityProvider
    )
    {
        $this->notificationResource = $notificationResource;
        $this->frontendIdentityProvider = $frontendIdentityProvider;
        $this->urlPlugin = $url;
    }

   /**
     * @return ViewActionResult
     */
    public function getInboxView()
    {
        return $this->getView(false);
    }

    /**
     * @return ViewActionResult
     */
    public function getArchiveView()
    {
        return $this->getView(true);
    }

    /**
     * @param $isArchive
     * @return ViewActionResult
     */
    private function getView($isArchive)
    {
        $userId = $this->frontendIdentityProvider->getIdentity()->getUserId();
        $result = new ViewActionResult();

        $vm = new NotificationListViewModel($this->urlPlugin);
        $vm
            ->setIsArchiveView($isArchive)
            ->setUnreadCount($this->notificationResource->getUnreadCount($userId))
            ->setNotifications($this->getNotifications($isArchive, $userId));

        $result->setViewModel($vm);
        $result->setTemplate(self::VIEW_TEMPLATE);
        $result->layout()->setPageTitle('Notifications');
        $result->layout()->setTemplate('layout/layout-govuk.phtml');
        $result->layout()->setBreadcrumbs(['Notifications' => null]);

        return $result;
    }

    /**
     * @param bool $isArchive
     * @param int $userId
     * @return \Dashboard\Model\Notification[]
     */
    private function getNotifications($isArchive, $userId)
    {
        return Notification::createList(
                $isArchive
                    ? $this->notificationResource->getArchivedNotifications($userId)
                    : $this->notificationResource->getInboxNotifications($userId)
        );
    }
}