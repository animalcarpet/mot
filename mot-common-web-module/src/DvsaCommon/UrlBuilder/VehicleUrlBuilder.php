<?php

namespace DvsaCommon\UrlBuilder;

/**
 * Class VehicleUrlBuilder.
 */
class VehicleUrlBuilder extends AbstractUrlBuilder
{
    const VEHICLE = 'vehicle[/:id]';
    const VEHICLE_LIST = 'vehicle/list';
    const VEHICLE_DVLA = 'vehicle-dvla/:id';

    const TEST_IN_PROGRESS_CHECK = '/test-in-progress-check';
    const TEST_EXPIRY_CHECK = '/test-expiry-check[/:isDvla]';
    const TEST_HISTORY = '/test-history';
    const RETEST_ELIGIBILITY_CHECK = '/retest-eligibility-check/[:siteId]';

    const SEARCH = 'vehicle-search';
    const TRAINING_SEARCH = 'training-test-vehicle-search';
    const NON_MOT_SEARCH = 'non-mot-test-vehicle-search';

    const MYSTERY_SHOPPER_CAMPAIGN = '/mystery-shopper-campaign';
    const MYSTERY_SHOPPER_DELETE = '/:incognitoVehicleId';
    const MYSTERY_SHOPPER_CURRENT = '/current';
    const MYSTERY_SHOPPER_EXTEND = '/extend';
    const MYSTERY_SHOPPER_LIST = '/list';

    protected $routesStructure
        = [
            self::VEHICLE      => [
                self::TEST_IN_PROGRESS_CHECK   => '',
                self::TEST_EXPIRY_CHECK        => '',
                self::TEST_HISTORY             => '',
                self::RETEST_ELIGIBILITY_CHECK => '',
                self::MYSTERY_SHOPPER_CAMPAIGN => [
                    self::MYSTERY_SHOPPER_DELETE => '',
                    self::MYSTERY_SHOPPER_CURRENT => '',
                    self::MYSTERY_SHOPPER_EXTEND => '',
                    self::MYSTERY_SHOPPER_LIST => '',
                ],
            ],
            self::VEHICLE_DVLA => '',
            self::SEARCH       => '',
            self::TRAINING_SEARCH  => '',
            self::NON_MOT_SEARCH  => '',
            self::VEHICLE_LIST => ''
        ];

    /**
     * @return VehicleUrlBuilder
     */
    public static function vehicle($id = null)
    {
        $url = self::of()->appendRoutesAndParams(self::VEHICLE);

        if ($id !== null) {
            $url->routeParam('id', $id);
        }

        return $url;
    }

    public static function vehicleList()
    {
        return self::of()->appendRoutesAndParams(self::VEHICLE_LIST);
    }

    /**
     * @param $vehicleId
     *
     * @return $this
     */
    public static function mysteryShopperCampaign($vehicleId)
    {
        return self::vehicle($vehicleId)
            ->appendRoutesAndParams(self::MYSTERY_SHOPPER_CAMPAIGN);
    }

    /**
     * @param $vehicleId
     *
     * @return $this
     */
    public static function mysteryShopperCurrent($vehicleId)
    {
        return self::mysteryShopperCampaign($vehicleId)
            ->appendRoutesAndParams(self::MYSTERY_SHOPPER_CURRENT);
    }

    /**
     * @param $vehicleId
     *
     * @return $this
     */
    public static function mysteryShopperExtend($vehicleId)
    {
        return self::mysteryShopperCampaign($vehicleId)
            ->appendRoutesAndParams(self::MYSTERY_SHOPPER_EXTEND);
    }

    /**
     * @param $vehicleId
     *
     * @return $this
     */
    public static function mysteryShopperList($vehicleId)
    {
        return self::mysteryShopperCampaign($vehicleId)
            ->appendRoutesAndParams(self::MYSTERY_SHOPPER_LIST);
    }

    public static function mysteryShopperDelete($vehicleId, $incognitoVehicleId)
    {
        return self::of()->mysteryShopperCampaign($vehicleId)
            ->appendRoutesAndParams(self::MYSTERY_SHOPPER_DELETE)
            ->routeParam('incognitoVehicleId', $incognitoVehicleId);
    }

    /**
     * @return VehicleUrlBuilder
     */
    public static function dvlaVehicle($id = null)
    {
        $url = self::of()->appendRoutesAndParams(self::VEHICLE_DVLA);

        if ($id !== null) {
            $url->routeParam('id', $id);
        }

        return $url;
    }

    public function testInProgressCheck()
    {
        return $this->appendRoutesAndParams(self::TEST_IN_PROGRESS_CHECK);
    }

    /**
     * @param bool $isDvlaVehicle
     *
     * @return $this
     */
    public static function testExpiryCheck($vehicleId, $isDvlaVehicle)
    {
        return self::vehicle($vehicleId)
            ->appendRoutesAndParams(self::TEST_EXPIRY_CHECK)
            ->routeParam('isDvla', (int) $isDvlaVehicle);
    }

    public static function testHistory($vehicleId, $siteId)
    {
        return self::vehicle($vehicleId)
            ->appendRoutesAndParams(self::TEST_HISTORY)
            ->queryParam('site', $siteId);
    }

    public static function retestEligibilityCheck($vehicleId, $siteId)
    {
        return self::vehicle($vehicleId)
            ->appendRoutesAndParams(self::RETEST_ELIGIBILITY_CHECK)
            ->routeParam('siteId', $siteId);
    }

    public static function search()
    {
        return self::of()->appendRoutesAndParams(self::SEARCH);
    }

    public static function trainingSearch()
    {
        return self::of()->appendRoutesAndParams(self::TRAINING_SEARCH);
    }

    public static function nonMotTestSearch()
    {
        return self::of()->appendRoutesAndParams(self::NON_MOT_SEARCH);
    }
}
