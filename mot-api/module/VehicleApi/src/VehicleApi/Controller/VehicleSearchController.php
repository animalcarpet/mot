<?php

namespace VehicleApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaEntities\DqlBuilder\SearchParam\VehicleSearchParam;
use VehicleApi\Service\VehicleSearchService;

/**
 * Class VehicleSearchController.
 */
class VehicleSearchController extends AbstractDvsaRestfulController
{
    const VIN_QUERY_PARAMETER = 'vin';
    const REG_QUERY_PARAMETER = 'reg';

    const SEARCH_PARAM_REQUIRED_DISPLAY_MESSAGE =
        'Missing Vehicle VRM/VIN number. Please enter a VRM/VIN to search for.';

    /**
     * @var VehicleSearchService
     */
    protected $vehicleSearchService;

    /**
     * @var VehicleSearchParam
     */
    protected $vehicleSearchParam;

    /**
     * @param VehicleSearchService $vehicleSearchService
     * @param VehicleSearchParam   $vehicleSearchParam
     */
    public function __construct(VehicleSearchService $vehicleSearchService, VehicleSearchParam $vehicleSearchParam)
    {
        $this->vehicleSearchService = $vehicleSearchService;
        $this->vehicleSearchParam = $vehicleSearchParam;
    }

    /**
     * Search for Vehicles by Registration or VIN.
     */
    public function getList()
    {
        try {
            $searchParam = $this->vehicleSearchParam;
            $vehicles = $this->vehicleSearchService->searchVehicleWithAdditionalData($searchParam);

            return ApiResponse::jsonOk($vehicles);
        } catch (\UnexpectedValueException $e) {
            return $this->returnBadRequestResponseModel(
                $e->getMessage(),
                self::ERROR_CODE_REQUIRED,
                self::SEARCH_PARAM_REQUIRED_DISPLAY_MESSAGE
            );
        }
    }
}
