<?php

namespace NotificationApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use NotificationApi\Service\NotificationService;

/**
 * Handles notification actions (nominations).
 */
class NotificationActionController extends AbstractDvsaRestfulController
{
    public function update($id, $data)
    {
        /** @var $service NotificationService */
        $service = $this->getServiceLocator()->get(NotificationService::class);
        $result = $service->action($id, $data);

        return ApiResponse::jsonOk($result);
    }
}
