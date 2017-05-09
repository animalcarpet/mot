<?php

namespace SiteApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\EquipmentService;

/**
 * Class EquipmentController.
 */
class EquipmentController extends AbstractDvsaRestfulController
{
    public function get($vtsId)
    {
        $service = $this->getEquipmentService();
        $dto = $service->getAllForSite($vtsId);

        return ApiResponse::jsonOk($dto);
    }

    /**
     * @return EquipmentService
     */
    private function getEquipmentService()
    {
        return $this->getServiceLocator()->get(EquipmentService::class);
    }
}
