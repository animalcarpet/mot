<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\DvlaVehicle;
use DvsaEntities\Entity\VehicleInterface;

/**
 * Class DvlaVehicleTest.
 */
class DvlaVehicleTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsVehicleInterface()
    {
        $r = new \ReflectionClass(DvlaVehicle::class);
        $this->assertTrue($r->implementsInterface(VehicleInterface::class));
    }

    public function testGetFirstUsedDateReturnsRegistrationDateIfNewAtRegistration()
    {
        $manufactureDate = \DateTime::createFromFormat('j-M-Y', '26-Jan-2015');
        $registrationDate = \DateTime::createFromFormat('j-M-Y', '18-May-2015');

        $dvlaVehicle = new DvlaVehicle();
        $dvlaVehicle
            ->setNewAtFirstReg(true)
            ->setManufactureDate($manufactureDate)
            ->setFirstRegistrationDate($registrationDate);

        $this->assertEquals($registrationDate, $dvlaVehicle->getFirstUsedDate());
    }

    public function testGetFirstUsedDateReturnsManufactureDateIfNotNewAtRegistration()
    {
        $manufactureDate = \DateTime::createFromFormat('j-M-Y', '26-Jan-2015');
        $registrationDate = \DateTime::createFromFormat('j-M-Y', '18-May-2015');

        $dvlaVehicle = new DvlaVehicle();
        $dvlaVehicle
            ->setNewAtFirstReg(false)
            ->setManufactureDate($manufactureDate)
            ->setFirstRegistrationDate($registrationDate);

        $this->assertEquals($manufactureDate, $dvlaVehicle->getFirstUsedDate());
    }

    public function testGetFirstUsedDateReturnsRegistrationDateIfNotNewAtRegistrationButNoManufactureDateIsAvailable()
    {
        $registrationDate = \DateTime::createFromFormat('j-M-Y', '18-May-2015');

        $dvlaVehicle = new DvlaVehicle();
        $dvlaVehicle
            ->setNewAtFirstReg(false)
            ->setFirstRegistrationDate($registrationDate);

        $this->assertEquals($registrationDate, $dvlaVehicle->getFirstUsedDate());
    }

    public function testIsVehicleNewAtFirstRegistrationReturnsBoolean()
    {
        $dvlaVehicle = new DvlaVehicle();

        $dvlaVehicle->setNewAtFirstReg(true);
        $this->assertTrue($dvlaVehicle->isVehicleNewAtFirstRegistration());

        $dvlaVehicle->setNewAtFirstReg(false);
        $this->assertFalse($dvlaVehicle->isVehicleNewAtFirstRegistration());
    }

    public function testInstanceKnowsItsType()
    {
        $dvlaVehicle = new DvlaVehicle();
        $this->assertTrue($dvlaVehicle->isDvla());
    }
}
