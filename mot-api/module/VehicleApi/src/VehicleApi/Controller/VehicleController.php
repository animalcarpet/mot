<?php

namespace VehicleApi\Controller;

use DateTime;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\MotTesting\ContingencyTestDto;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use VehicleApi\Service\VehicleSearchService;
use VehicleApi\Service\VehicleService;

/**
 * Class VehicleController.
 */
class VehicleController extends AbstractDvsaRestfulController
{
    const VIN_QUERY_PARAMETER = 'vin';
    const REG_QUERY_PARAMETER = 'reg';
    const VTS_ID_QUERY_PARAMETER = 'vtsId';
    const EXCLUDE_DVLA_PARAMETER = 'excludeDvla';
    const CONTINGENCY_DATETIME_QUERY_PARAMETER = 'contingencyDatetime';
    const VIN_REG_REQUIRED_MESSAGE = 'Query parameter vin or reg is required';
    const VIN_REG_REQUIRED_DISPLAY_MESSAGE = 'You need to enter the vehicle registration mark or VIN';
    const TOO_LONG_ERROR_MESSAGE = 'Query parameter %s is more than %d characters long';
    const VIN_TOO_LONG_DISPLAY_MESSAGE = 'The VIN is more than %d characters long';
    const REG_TOO_LONG_DISPLAY_MESSAGE = 'The vehicle registration mark is more than %d characters long';
    const VIN_LENGTH = 20;
    const REG_LENGTH = 13;

    /**
     * @var VehicleService
     */
    protected $vehicleSearch;

    /**
     * @var VehicleSearchService
     */
    private $vehicleSearchService;

    /**
     * VehicleController constructor.
     *
     * @param VehicleService       $vehicleService
     * @param VehicleSearchService $vehicleSearchService
     */
    public function __construct(VehicleService $vehicleService, VehicleSearchService $vehicleSearchService)
    {
        $this->vehicleSearch = $vehicleService;
        $this->vehicleSearchService = $vehicleSearchService;
    }

    public function get($id)
    {
        $data = $this->vehicleSearch->getVehicleDto($id);

        return ApiResponse::jsonOk($data);
    }

    public function getList()
    {
        $vehicle = null;
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $vin = $this->sanitize((string) $request->getQuery(self::VIN_QUERY_PARAMETER, ''));
        $reg = $this->sanitize((string) $request->getQuery(self::REG_QUERY_PARAMETER, ''));
        $vtsId = $this->sanitize((string) $request->getQuery(self::VTS_ID_QUERY_PARAMETER, ''));

        $contingencyDatetime = $this->sanitize((string) $request->getQuery(self::CONTINGENCY_DATETIME_QUERY_PARAMETER, ''));

        $contingencyDto = false;
        if (!empty($contingencyDatetime)) {
            $contingencyDatetime = DateUtils::toDateTime($contingencyDatetime);
            $contingencyDto = new ContingencyTestDto();
            $contingencyDto->setPerformedAt(($contingencyDatetime instanceof DateTime) ? $contingencyDatetime : null);
        }

        $falseList = [false, 0, 'false', '0'];
        $excludeDvlaInQuery = $request->getQuery(self::EXCLUDE_DVLA_PARAMETER, false);

        $excludeDvla = in_array($excludeDvlaInQuery, $falseList) ? false : true;

        $vehiclesData = $this
            ->vehicleSearchService
            ->searchVehicleWithMotData($vin, $reg, $excludeDvla, $vtsId, $contingencyDto);

        return ApiResponse::jsonOk(['vehicles' => $vehiclesData]);
    }

    /**
     * Sanitize vin or reg.
     *
     * @param $string
     *
     * @return string
     */
    private function sanitize($string)
    {
        return strtoupper($string);
    }
}
