<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleInterface;

/**
 * Class VehicleTest.
 */
class VehicleTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsVehicleInterface()
    {
        $r = new \ReflectionClass(Vehicle::class);
        $this->assertTrue($r->implementsInterface(VehicleInterface::class));
    }

    public function testIsVehicleNewAtFirstRegistrationReturnsBoolean()
    {
        $vehicle = new Vehicle();

        $vehicle->setNewAtFirstReg(true);
        $this->assertTrue($vehicle->isVehicleNewAtFirstRegistration());

        $vehicle->setNewAtFirstReg(false);
        $this->assertFalse($vehicle->isVehicleNewAtFirstRegistration());
    }

    public function testInstanceKnowsItsType()
    {
        $dvlaVehicle = new Vehicle();
        $this->assertFalse($dvlaVehicle->isDvla());
    }
}
