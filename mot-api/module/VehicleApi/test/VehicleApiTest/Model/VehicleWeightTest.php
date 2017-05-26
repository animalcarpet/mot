<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace VehicleApiTest\Model;

use VehicleApi\Model\VehicleWeight;

class VehicleWeightTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider vehicleWeightAndWeightSourceDataProvider
     *
     * @param int|null $weight
     * @param int      $weightSource
     * @param bool     $expected
     */
    public function testHasWeightAndWeightSourceMethods($weight, $weightSource, $expected)
    {
        $vehicleWeight = new VehicleWeight();

        $this->assertFalse($vehicleWeight->hasWeight() && $vehicleWeight->hasWeightSource());

        $vehicleWeight->setWeight($weight)->setWeightSource($weightSource);

        $this->assertEquals($vehicleWeight->hasWeight() && $vehicleWeight->hasWeightSource(), $expected);
    }

    public function vehicleWeightAndWeightSourceDataProvider()
    {
        $validWeightSource = 2;

        /* Negative weight has not been tested, as it will pass this test.
           Negative weights are caught/tested in api-client-php (src/Request/AbstractDvlaVehicleRequest.php) */
        return [
            [0, $validWeightSource, false],
            [null, $validWeightSource, false],
            [10000, $validWeightSource, true],
        ];
    }
}
