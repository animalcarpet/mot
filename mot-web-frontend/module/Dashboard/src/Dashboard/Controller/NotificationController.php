<?php
namespace Dashboard\Controller;

use Core\Controller\AbstractAuthActionController;
use Dashboard\Action\NotificationAction;
use Dashboard\Data\ApiNotificationResource;
use Dashboard\Model\Notification;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Date\DateTimeDisplayFormat;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class NotificationController extends AbstractAuthActionController implements AutoWireableInterface
{
    const ROUTE_NOTIFICATION = 'user-home/notification/item';
    const ROUTE_NOTIFICATION_LIST = 'user-home/notification/list';
    const ROUTE_NOTIFICATION_ARCHIVE = 'user-home/notification/list/archive';
    const ROUTE_NOTIFICATION_ACTION = 'user-home/notification/action';
    const ROUTE_NOTIFICATION_ACTION_ARCHIVE = 'user-home/notification/archive';
    const ROUTE_USER_HOME = 'user-home';
    const MESSAGE_NOTIFICATION_ARCHIVED = "Notification has been archived";
    const BACK_TO_ARCHIVE_PARAM = "archive";
    const BACK_TO_HOME_PARAM = "home";

    const SHORT_LIST_LIMIT = 5;

    private $apiNotificationResource;
    private $notificationAction;

    public function __construct(
        ApiNotificationResource $apiNotificationResource,
        NotificationAction $notificationAction
    )
    {
        $this->apiNotificationResource = $apiNotificationResource;
        $this->notificationAction = $notificationAction;
    }

    public function inboxAction()
    {
        return $this->applyActionResult($this->notificationAction->getInboxView());
    }

    public function archiveAction()
    {
        return $this->applyActionResult($this->notificationAction->getArchiveView());
    }

    public function notificationAction()
    {
        $notificationId = $this->params()->fromRoute('notificationId', null);

        if ($notificationId) {
            $notificationApiResponse = $this->apiNotificationResource->markAsRead($notificationId);
            $notification = new Notification($notificationApiResponse);

            $this->layout('layout/layout-govuk.phtml');
            $this->layout()->setVariable('pageTitle', $notification->getSubject());
            $this->layout()->setVariable('pageTertiaryTitle', DateTimeDisplayFormat::textDate($notification->getCreatedOn()));
            $this->layout()->setVariable("breadcrumbs", ["breadcrumbs" => ["Notifications" => ""]]);

            $viewModel = new ViewModel();
            $viewModel->setVariable('notification', $notification);
            $viewModel->setVariable("backLinkUrl", $this->getBackLinkUrl());
            $viewModel->setVariable("backLinkLabel", $this->getBackLinkLabel());

            if ($notification->isActionRequired()) {
                $viewModel->setTemplate('dashboard/notification/notification_action.phtml');
            } else {
                $viewModel->setTemplate('dashboard/notification/notification_general.phtml');
            }

            return $viewModel;
        }

        throw new \Exception('Notification not found');
    }

    private function getBackLinkUrl()
    {
        $backTo = $this->getRequest()->getQuery("backTo");
        switch ($backTo) {
            case self::BACK_TO_HOME_PARAM:
                $url = self::ROUTE_USER_HOME;
                break;
            case self::BACK_TO_ARCHIVE_PARAM:
                $url = self::ROUTE_NOTIFICATION_ARCHIVE;
                break;
            default:
                $url = self::ROUTE_NOTIFICATION_LIST;
        }

        return $url;
    }

    private function getBackLinkLabel()
    {
        $backTo = $this->getRequest()->getQuery("backTo");
        if ($backTo === self::BACK_TO_HOME_PARAM) {
            $label = "Back to home";
        } else {
            $label = "Back to notifications";
        }

        return $label;
    }


    public function archiveNotificationAction()
    {
        $params = $this->getRequest()->getPost();
        $this->apiNotificationResource->archive($params->get('notificationId'));
        $this->flashMessenger()->addSuccessMessage(self::MESSAGE_NOTIFICATION_ARCHIVED);

        return $this->redirect()->toUrl($params->get('url'));
    }

    public function confirmNominationAction()
    {
        /** @var $request Request */
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();

            if (isset($postData['notificationId']) && isset($postData['action']) && isset($postData['url'])) {
                $personId = $this->getIdentity()->getUserId();

                $this->apiNotificationResource->notificationAction($personId, $postData['notificationId'], $postData['action']);

                return $this->redirect()->toUrl($postData['url']);
            }
            throw new \RuntimeException('Wrong data');
        }
        throw new \RuntimeException('Method not allowed');
    }
}
