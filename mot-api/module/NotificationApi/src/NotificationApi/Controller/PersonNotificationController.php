<?php
namespace NotificationApi\Controller;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use NotificationApi\Mapper\NotificationMapper;
use NotificationApi\Service\NotificationService;

/**
 * Class PersonNotificationController
 *
 * @package NotificationApi\Controller
 */
class PersonNotificationController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function create($data)
    {
        $result = $this->notificationService->add($data);
        $extractor = new NotificationMapper();

        return ApiResponse::jsonOk($extractor->toArray($this->notificationService->get($result)));
    }

    public function getList()
    {
        $personId = (int) $this->params()->fromRoute('personId', null);
        $fetchArchived = (bool) $this->params()->fromQuery('archived', false);
        $extractedResult = [];

        $notifications = $fetchArchived
            ? $this->notificationService->getAllArchivedByPersonId($personId)
            : $this->notificationService->getAllInboxByPersonId($personId);


        if (is_array($notifications)) {
            $extractor = new NotificationMapper();
            foreach ($notifications as $notification) {
                $extractedResult[] = $extractor->toArray($notification);
            }
        }

        return ApiResponse::jsonOk($extractedResult);
    }

    public function unreadCountAction()
    {
        $personId = (int) $this->params()->fromRoute('personId', null);

        return ApiResponse::jsonOk((int) $this->notificationService->getUnreadCountByPersonId($personId));
    }
}
