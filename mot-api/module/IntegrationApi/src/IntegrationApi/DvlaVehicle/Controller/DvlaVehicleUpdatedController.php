<?php

namespace IntegrationApi\DvlaVehicle\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Service\Exception\NotFoundException;
use IntegrationApi\DvlaVehicle\Service\DvlaVehicleUpdatedService;
use Zend\View\Model\JsonModel;

/**
 * Class DvlaVehicleUpdatedController
 */
class DvlaVehicleUpdatedController extends AbstractDvsaRestfulController
{
    private $dvlaVehicleUpdatedService;

    public function __construct(DvlaVehicleUpdatedService $dvlaVehicleUpdatedService)
    {
        $this->dvlaVehicleUpdatedService = $dvlaVehicleUpdatedService;
    }

    /**
     * @param array $data
     * @return JsonModel
     * @throws NotFoundException
     */
    public function create($data)
    {
        if (empty($data['vehicleId'])) {
            throw new NotFoundException('vehicleId');
        }

        $isDvlaImport = true;

        $this->dvlaVehicleUpdatedService->createReplacementCertificate($data['vehicleId'], $this->getUserId(), $isDvlaImport);
        return ApiResponse::jsonOk();
    }
}
