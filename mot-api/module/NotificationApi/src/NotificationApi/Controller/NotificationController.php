<?php
namespace NotificationApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use NotificationApi\Mapper\NotificationMapper;
use NotificationApi\Service\NotificationService;

/**
 * Class NotificationController
 *
 * @package NotificationApi\Controller
 */
class NotificationController extends AbstractDvsaRestfulController
{

    public function update($id, $data)
    {
        $service = $this->getNotificationService();
        $result = $service->markAsRead($id);

        $extractor = new NotificationMapper();
        return ApiResponse::jsonOk($extractor->toArray($result));
    }

    public function get($id)
    {
        $service = $this->getNotificationService();
        $result = $service->get($id);

        $extractor = new NotificationMapper();

        return ApiResponse::jsonOk($extractor->toArray($result));
    }

    public function delete($id)
    {
        $service = $this->getNotificationService();
        $result = $service->delete($id);

        return ApiResponse::jsonOk($result);
    }

    public function create($data)
    {
        $service = $this->getNotificationService();
        $result = $service->add($data);

        return ApiResponse::jsonOk($result);
    }

    /**
     * @return NotificationService
     */
    private function getNotificationService()
    {
        return $this->getServiceLocator()->get(NotificationService::class);
    }
}
