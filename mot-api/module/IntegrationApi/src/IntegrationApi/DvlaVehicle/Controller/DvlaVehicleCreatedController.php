<?php

namespace IntegrationApi\DvlaVehicle\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use Zend\View\Model\JsonModel;

/**
 * Class DvlaVehicleCreatedController.
 */
class DvlaVehicleCreatedController extends AbstractDvsaRestfulController
{
    /**
     * @todo Create entries in 'vehicle' table with data from 'dvla_vehicle' ('dvla_vehicle.id' set sent in $data)
     *
     * @param array $data
     *
     * @return JsonModel
     */
    public function create($data)
    {
        return ApiResponse::jsonOk('');
    }
}
