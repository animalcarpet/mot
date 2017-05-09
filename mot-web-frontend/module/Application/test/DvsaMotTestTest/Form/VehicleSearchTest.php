<?php

namespace DvsaMotTest\Form;

use PHPUnit_Framework_TestCase;

/**
 * Class VehicleSearchTest.
 */
class VehicleSearchTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $vehicleSearch = new VehicleSearch();

        $this->assertNull($vehicleSearch->registration, '"registration" should initially be null');
        $this->assertNull($vehicleSearch->vin, '"vin" should initially be null');
    }

    public function testExchangeArraySetsPropertiesCorrectly()
    {
        $vehicleSearch = new VehicleSearch();
        $data = self::getTestVehicleSearchData();

        $vehicleSearch->exchangeArray($data);

        $this->assertSame(
            $data['registration'],
            $vehicleSearch->registration,
            '"registration" was not set correctly'
        );
        $this->assertSame(
            $data['vin'],
            $vehicleSearch->vin,
            '"vin" was not set correctly'
        );
    }

    public function testExchangeArraySetsPropertiesToDefaultIfKeysAreNotPresent()
    {
        $vehicleSearch = new VehicleSearch();
        $vehicleSearch->exchangeArray([]);

        $this->assertNull($vehicleSearch->registration, '"registration" should have defaulted to null');
        $this->assertNull($vehicleSearch->vin, '"vin" should have defaulted to null');
    }

    public static function getTestVehicleSearchData()
    {
        return [
            'registration' => 'testreg',
            'vin' => '123456',
            'vinType' => 'partialVin',
        ];
    }
}
