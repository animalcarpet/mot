<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\IncognitoVehicle;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use PHPUnit_Framework_TestCase;

class IncognitoVehicleTest extends PHPUnit_Framework_TestCase
{
    /** @var IncognitoVehicle */
    private $incognitoVehicle;

    public function setUp()
    {
        $this->incognitoVehicle = new IncognitoVehicle();
    }

    public function testInitialState()
    {
        $this->assertNull($this->incognitoVehicle->getEndDate());
        $this->assertNull($this->incognitoVehicle->getVehicle());
        $this->assertNull($this->incognitoVehicle->getExpiryDate());
        $this->assertNull($this->incognitoVehicle->getId());
        $this->assertNull($this->incognitoVehicle->getPerson());
        $this->assertNull($this->incognitoVehicle->getSite());
        $this->assertNull($this->incognitoVehicle->getStartDate());
        $this->assertNull($this->incognitoVehicle->getTestDate());
    }

    /**
     * @covers DvsaEntities\Entity\Vehicle::setEndDate
     * @covers DvsaEntities\Entity\Vehicle::setExpiryDate
     * @covers DvsaEntities\Entity\Vehicle::setPerson
     * @covers DvsaEntities\Entity\Vehicle::setSite
     * @covers DvsaEntities\Entity\Vehicle::setStartDate
     * @covers DvsaEntities\Entity\Vehicle::setVehicle
     * @covers DvsaEntities\Entity\Vehicle::setTestDate
     * @covers DvsaEntities\Entity\Vehicle::getEndDate
     * @covers DvsaEntities\Entity\Vehicle::getStartDate
     * @covers DvsaEntities\Entity\Vehicle::getTestDate
     * @covers DvsaEntities\Entity\Vehicle::getExpiryDate
     * @covers DvsaEntities\Entity\Vehicle::getPerson
     * @covers DvsaEntities\Entity\Vehicle::getVehicle
     * @covers DvsaEntities\Entity\Vehicle::getSite
     */
    public function testGettersAndSetters()
    {
        $date = new \DateTime("2015-07-08");
        $this->incognitoVehicle
            ->setEndDate($date)
            ->setExpiryDate($date)
            ->setPerson(new Person())
            ->setSite(new Site())
            ->setStartDate($date)
            ->setVehicle(new Vehicle())
            ->setTestDate($date);

        $this->assertSame($date, $this->incognitoVehicle->getEndDate());
        $this->assertSame($date, $this->incognitoVehicle->getStartDate());
        $this->assertSame($date, $this->incognitoVehicle->getTestDate());
        $this->assertSame($date, $this->incognitoVehicle->getExpiryDate());
        $this->assertInstanceOf(Person::class, $this->incognitoVehicle->getPerson());
        $this->assertInstanceOf(Vehicle::class, $this->incognitoVehicle->getVehicle());
        $this->assertInstanceOf(Site::class, $this->incognitoVehicle->getSite());
    }
}