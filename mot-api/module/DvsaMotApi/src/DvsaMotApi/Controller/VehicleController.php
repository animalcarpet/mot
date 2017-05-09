<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use VehicleApi\Service\VehicleSearchService;
use VehicleApi\Service\VehicleService;

/**
 * Class VehicleController.
 *
 * @deprecated Use VehicleApi/VehicleController
 */
class VehicleController extends AbstractDvsaRestfulController
{
    const VIN_QUERY_PARAMETER = 'vin';
    const REG_QUERY_PARAMETER = 'reg';
    const VIN_TYPE_PARAMETER = 'vinType';
    const EXCLUDE_DVLA_PARAMETER = 'excludeDvla';

    const FULL_VIN = 'fullVin';
    const PARTIAL_VIN = 'partialVin';
    const NO_VIN = 'noVin';

    const SEARCH_RESULT_NO_MATCH = 'NO-MATCH';
    const SEARCH_RESULT_EXACT_MATCH = 'EXACT-MATCH';
    const SEARCH_RESULT_MULTIPLE_MATCHES = 'MULTIPLE-MATCHES';
    const SEARCH_RESULT_TOO_MANY_MATCHES = 'TOO-MANY-MATCHES';

    const INCORRECT_PARTIAL_VIN_ERROR_MESSAGE
        = 'Query parameter vin must be a partial VIN (last 6 characters)';
    const VIN_REQUIRED_MESSAGE = 'Query parameter vin is required';
    const VIN_TYPE_REQUIRED_MESSAGE = 'Query parameter vinType is required';
    const REG_REQUIRED_MESSAGE = 'Query parameter partial vin, then parameter reg is required';
    const REG_REQUIRED_NO_VIN_MESSAGE = 'Query parameter no vin, then parameter reg is required';
    const INCORRECT_PARTIAL_VIN_DISPLAY_MESSAGE = 'VIN is incorrect. Please enter the last six valid characters';
    const VIN_REQUIRED_DISPLAY_MESSAGE = 'You need to enter the VIN to perform the search';
    const VIN_TYPE_REQUIRED_DISPLAY_MESSAGE = 'You need to supply the VIN type to perform the search';
    const REG_REQUIRED_DISPLAY_MESSAGE = 'You need to enter registration to search on a partial VIN';
    const REG_REQUIRED__NO_VIN_DISPLAY_MESSAGE = 'You need to enter the vehicle registration mark to search without a VIN';

    public function create($data)
    {
        $data = $this->sanitizeArray($data, ['vin', 'registrationNumber']);

        return ApiResponse::jsonOk($this->getVehicleService()->create($data));
    }

    public function getList()
    {
        $vehicle = null;
        $request = $this->getRequest();

        $vin = $this->sanitize((string) $request->getQuery(self::VIN_QUERY_PARAMETER));
        $reg = $this->sanitize((string) $request->getQuery(self::REG_QUERY_PARAMETER));
        $vinType = (string) $request->getQuery(self::VIN_TYPE_PARAMETER);
        $searchDvla = $request->getQuery(self::EXCLUDE_DVLA_PARAMETER) != 'true';

        if (!$vinType) {
            return $this->returnBadRequestResponseModel(
                self::VIN_TYPE_REQUIRED_MESSAGE,
                self::ERROR_CODE_REQUIRED,
                self::VIN_TYPE_REQUIRED_DISPLAY_MESSAGE
            );
        }
        if ($vinType === self::PARTIAL_VIN || $vinType === self::FULL_VIN and !$vin) {
            return $this->returnBadRequestResponseModel(
                self::VIN_REQUIRED_MESSAGE,
                self::ERROR_CODE_REQUIRED,
                self::VIN_REQUIRED_DISPLAY_MESSAGE
            );
        }
        if ($vinType === self::PARTIAL_VIN and strlen($vin) < 6) {
            return $this->returnBadRequestResponseModel(
                self::INCORRECT_PARTIAL_VIN_ERROR_MESSAGE,
                self::ERROR_CODE_REQUIRED,
                self::INCORRECT_PARTIAL_VIN_DISPLAY_MESSAGE
            );
        }
        if ($vinType === self::PARTIAL_VIN and !$reg) {
            return $this->returnBadRequestResponseModel(
                self::REG_REQUIRED_MESSAGE,
                self::ERROR_CODE_REQUIRED,
                self::REG_REQUIRED_DISPLAY_MESSAGE
            );
        }
        if ($vinType === self::NO_VIN and !$reg) {
            return $this->returnBadRequestResponseModel(
                self::REG_REQUIRED_NO_VIN_MESSAGE,
                self::ERROR_CODE_REQUIRED,
                self::REG_REQUIRED__NO_VIN_DISPLAY_MESSAGE
            );
        }

        $service = $this->getVehicleSearchService();

        list($vehiclesData, $exactMatch) = $service->search($vin, $reg, $vinType == self::FULL_VIN, $searchDvla, 6);
        $numberOfVehicles = count($vehiclesData);

        if ($numberOfVehicles == 0) {
            return ApiResponse::jsonOk(
                [
                    'resultType' => self::SEARCH_RESULT_NO_MATCH,
                ]
            );
        } else {
            if ($numberOfVehicles == 1 && $exactMatch) {
                return ApiResponse::jsonOk(
                    [
                        'resultType' => self::SEARCH_RESULT_EXACT_MATCH,
                        'vehicle' => $vehiclesData[0],
                    ]
                );
            } else {
                if ($numberOfVehicles <= 5) {
                    return ApiResponse::jsonOk(
                        [
                            'resultType' => self::SEARCH_RESULT_MULTIPLE_MATCHES,
                            'resultCount' => $numberOfVehicles,
                            'vehicles' => $vehiclesData,
                        ]
                    );
                } else {
                    return ApiResponse::jsonOk(
                        [
                            'resultType' => self::SEARCH_RESULT_TOO_MANY_MATCHES,
                            'resultCount' => $numberOfVehicles,
                        ]
                    );
                }
            }
        }
    }

    /**
     * @return VehicleService
     */
    private function getVehicleService()
    {
        return $this->getServiceLocator()->get(VehicleService::class);
    }

    /**
     * @return VehicleSearchService
     */
    private function getVehicleSearchService()
    {
        return $this->getServiceLocator()->get(VehicleSearchService::class);
    }

    /**
     * Sanitize vin or reg.
     *
     * @param $string
     *
     * @return string
     */
    protected function sanitize($string)
    {
        return strtoupper($string);
    }

    /**
     * @param $array
     * @param $keys string[]
     */
    protected function sanitizeArray($array, $keys)
    {
        foreach ($keys as $k) {
            if (isset($array[$k])) {
                $array[$k] = $this->sanitize($array[$k]);
            }
        }

        return $array;
    }
}
