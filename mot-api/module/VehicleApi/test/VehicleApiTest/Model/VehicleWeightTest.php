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
    public function testHasWeighAndWeightSourceMethods()
    {
        $vehicleWeight = new VehicleWeight();

        $this->assertFalse($vehicleWeight->hasWeight() && $vehicleWeight->hasWeightSource());

        $vehicleWeight->setWeight(0)
            ->setWeightSource(0);

        $this->assertTrue($vehicleWeight->hasWeight() && $vehicleWeight->hasWeightSource());
    }
}
