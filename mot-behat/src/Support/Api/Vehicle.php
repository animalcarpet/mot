<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultVehicleTestingStation;
use Dvsa\Mot\Behat\Support\HttpClient;
use Dvsa\Mot\Behat\Support\Request;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\CountryOfRegistrationId;
use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommon\Enum\VehicleClassCode;

class Vehicle extends MotApi
{
    const PATH = 'vehicle';
    const PATH_ID = 'vehicle/{vehicle_id}';
    const PATH_SEARCH = 'vehicle/list?reg={vehicle_reg}&vinType=noVin&excludeDvla=1';
    const PATH_SEARCH_WITH_VIN = 'vehicle/list?reg={vehicle_reg}&vinType=fullVin&vin={vin}&excludeDvla=1';
    const PATH_SEARCH_WITH_DVLA = 'vehicle/list?reg={vehicle_reg}&vinType=noVin&excludeDvla=0';
    const PATH_SEARCH_WITH_VIN_AND_DVLA = 'vehicle/list?reg={vehicle_reg}&vinType=fullVin&vin={vin}&excludeDvla=0';

    /**
     * Search for a vehicle with the given details
     *
     * @param string           $token
     * @param string           $reg
     * @param string|null      $vin
     * @param bool|false       $searchDvla
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function vehicleSearch($token, $reg, $vin = null, $searchDvla = false)
    {
        if (empty($vin)) {
            if ($searchDvla) {
                $url = str_replace('{vehicle_reg}', $reg, self::PATH_SEARCH_WITH_DVLA);
            } else {
                $url = str_replace('{vehicle_reg}', $reg, self::PATH_SEARCH);
            }
        } else {
            if ($searchDvla) {
                $url = str_replace(['{vehicle_reg}', '{vin}'], [$reg, $vin], self::PATH_SEARCH_WITH_VIN_AND_DVLA);
            } else {
                $url = str_replace(['{vehicle_reg}', '{vin}'], [$reg, $vin], self::PATH_SEARCH_WITH_VIN);
            }

        }

        return $this->sendGetRequest(
            $token,
            $url
        );
    }

    /**
     * Create a certificate_replacement entry for the updated dvla_vehicle details
     *
     * @param string $token
     * @param int    $vehicleId
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function dvlaVehicleUpdated($token, $vehicleId)
    {
        return $this->sendPostRequest(
            $token,
            'dvla-vehicle-updated',
            ['vehicleId' => $vehicleId]
        );
    }

    public function randomRegNumber($length = 7)
    {
        $str = '';
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }

        return strtoupper($str);
    }

    public function randomVin($length = 17)
    {
        $str = '';
        $characters = array_merge(range('0', '9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }

        return $str;
    }

    /**
     * Retrieve the vehicle details associated with the given $vehicleId
     *
     * @param string $token
     * @param string $vehicleId
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function getVehicleDetails($token, $vehicleId)
    {
        return $this->sendGetRequest(
            $token,
            str_replace('{vehicle_id}', $vehicleId, self::PATH_ID)
        );
    }
}
