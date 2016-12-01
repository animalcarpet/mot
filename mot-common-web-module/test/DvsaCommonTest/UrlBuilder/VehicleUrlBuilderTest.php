<?php

namespace DvsaCommonTest\UrlBuilder;

use DvsaCommon\UrlBuilder\AbstractUrlBuilder;
use DvsaCommon\UrlBuilder\VehicleUrlBuilder;

class VehicleUrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    const MOT_TEST_NR = '123456798';
    const TEST_TYPE_CODE = 'NT';
    const VEHICLE_ID = 9999;
    const IS_DVLA = true;
    const SITE_ID = 8888;

    public function testVehicle()
    {
        $base = 'vehicle/' . self::VEHICLE_ID;
        $this->checkUrl(VehicleUrlBuilder::vehicle(self::VEHICLE_ID), $base);
        $this->checkUrl(
            VehicleUrlBuilder::testExpiryCheck(self::VEHICLE_ID, self::IS_DVLA),
            $base . '/test-expiry-check/' . self::IS_DVLA
        );

        $this->checkUrl(VehicleUrlBuilder::testHistory(self::VEHICLE_ID), $base . '/test-history');
        $this->checkUrl(
            VehicleUrlBuilder::retestEligibilityCheck(self::VEHICLE_ID, self::SITE_ID),
            $base . '/retest-eligibility-check/' . self::SITE_ID
        );

        $this->checkUrl(VehicleUrlBuilder::dvlaVehicle(self::VEHICLE_ID), 'vehicle-dvla/' . self::VEHICLE_ID);

        $this->checkUrl(VehicleUrlBuilder::vehicleList(self::MOT_TEST_NR), 'vehicle/list');
        $this->checkUrl(VehicleUrlBuilder::search(self::MOT_TEST_NR), 'vehicle-search');

        $this->checkUrl(
            VehicleUrlBuilder::vehicle(self::VEHICLE_ID)->testInProgressCheck(),
            $base . '/test-in-progress-check'
        );
    }

    public function testMysteryShopper()
    {
        $base = 'vehicle/' . self::VEHICLE_ID;
        $incognitoId = 1;

        $this->checkUrl(
            VehicleUrlBuilder::mysteryShopperCampaign(self::VEHICLE_ID),
            $base . VehicleUrlBuilder::MYSTERY_SHOPPER_CAMPAIGN
        );

        $this->checkUrl(
            VehicleUrlBuilder::mysteryShopperCurrent(self::VEHICLE_ID),
            $base . VehicleUrlBuilder::MYSTERY_SHOPPER_CAMPAIGN . VehicleUrlBuilder::MYSTERY_SHOPPER_CURRENT
        );

        $this->checkUrl(
            VehicleUrlBuilder::mysteryShopperDelete(self::VEHICLE_ID, $incognitoId),
            $base . VehicleUrlBuilder::MYSTERY_SHOPPER_CAMPAIGN . '/' . $incognitoId
        );

        $this->checkUrl(
            VehicleUrlBuilder::mysteryShopperExtend(self::VEHICLE_ID),
            $base . VehicleUrlBuilder::MYSTERY_SHOPPER_CAMPAIGN . VehicleUrlBuilder::MYSTERY_SHOPPER_EXTEND
        );

        $this->checkUrl(
            VehicleUrlBuilder::mysteryShopperList(self::VEHICLE_ID),
            $base . VehicleUrlBuilder::MYSTERY_SHOPPER_CAMPAIGN . VehicleUrlBuilder::MYSTERY_SHOPPER_LIST
        );
    }

    private function checkUrl(AbstractUrlBuilder $urlBuilder, $expectUrl)
    {
        $this->assertEquals($expectUrl, $urlBuilder->toString());
        $this->assertInstanceOf(VehicleUrlBuilder::class, $urlBuilder);
    }
}
