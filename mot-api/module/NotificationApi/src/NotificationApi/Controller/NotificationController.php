<?php
namespace NotificationApi\Controller;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use NotificationApi\Mapper\NotificationMapper;
use NotificationApi\Service\NotificationService;

/**
 * Class NotificationController
 *
 * @package NotificationApi\Controller
 */
class NotificationController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    /**
     * @var NotificationService
     */
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function update($id, $data)
    {
        $result = $this->notificationService->markAsRead($id);
        $extractor = new NotificationMapper();

        return ApiResponse::jsonOk($extractor->toArray($result));
    }

    public function get($id)
    {
        $result = $this->notificationService->get($id);
        $extractor = new NotificationMapper();

        return ApiResponse::jsonOk($extractor->toArray($result));
    }

    public function delete($id)
    {
        return ApiResponse::jsonOk($this->notificationService->delete($id));
    }

    public function create($data)
    {
        return ApiResponse::jsonOk($this->notificationService->add($data));
    }

    public function archiveAction()
    {
        $id = $this->params()->fromRoute($this->identifierName);
        return ApiResponse::jsonOk($this->notificationService->archive((int)$id));
    }
}
