<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace VehicleTest\ViewModel;

use PHPUnit_Framework_TestCase;
use Vehicle\ViewModel\VehicleViewModel;

class VehicleViewModelTest extends PHPUnit_Framework_TestCase
{
    public function testShouldDisplayVehicleMaskedBanner()
    {
        $vehicleViewModel = new VehicleViewModel();

        // Initial state
        $this->assertFalse($vehicleViewModel->shouldDisplayVehicleMaskedBanner());

        $vehicleViewModel->setShouldDisplayVehicleMaskedBanner(true);
        $this->assertTrue($vehicleViewModel->shouldDisplayVehicleMaskedBanner());

        $vehicleViewModel->setShouldDisplayVehicleMaskedBanner(false);
        $this->assertFalse($vehicleViewModel->shouldDisplayVehicleMaskedBanner());
    }

    public function testGetObfuscatedVehicleId()
    {
        $vehicleViewModel = new VehicleViewModel();

        // Initial state
        $this->assertNull($vehicleViewModel->getObfuscatedVehicleId());

        $vehicleViewModel->setObfuscatedVehicleId('1w');
        $this->assertEquals('1w', $vehicleViewModel->getObfuscatedVehicleId());
    }
}
