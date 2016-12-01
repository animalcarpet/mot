<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Dto\Vehicle\History\VehicleHistoryMapper;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaMotApi\Service\VehicleHistoryService;

class VehicleHistoryController extends AbstractDvsaRestfulController
{
    public function get($vehicleId)
    {
        $history = $this->getVehicleHistoryService()->findHistoricalTestsForVehicleSince($vehicleId, $this->getUserId());

        return ApiResponse::jsonOk((new VehicleHistoryMapper)->fromDtoToArray($history));
    }

    /**
     * @return VehicleHistoryService
     */
    private function getVehicleHistoryService()
    {
        return $this->getServiceLocator()->get(VehicleHistoryService::class);
    }
}
