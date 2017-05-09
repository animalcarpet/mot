<?php

namespace VehicleApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use VehicleApi\Service\VehicleService;

/**
 * Class VehicleDvlaController.
 */
class VehicleDvlaController extends AbstractDvsaRestfulController
{
    public function get($id)
    {
        $data = $this->getVehicleService()->getDvlaVehicleData($id);

        return ApiResponse::jsonOk($data);
    }

    /**
     * @return VehicleService
     */
    private function getVehicleService()
    {
        return $this->getServiceLocator()->get(VehicleService::class);
    }
}
