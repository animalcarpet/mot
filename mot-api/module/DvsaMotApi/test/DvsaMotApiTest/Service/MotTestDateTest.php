<?php

namespace DvsaMotApiTest\Service;

use PHPUnit_Framework_ExpectationFailedException;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Service\MotTestDate;
use DvsaEntities\Entity\DvlaVehicle;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Entity\MotTest;

class MotTestDateTest extends AbstractServiceTestCase
{
    /** @var  MotTest */
    protected $motCurrent;
    /** @var  MotTest */
    protected $motPrevious;
    /** @var  Vehicle */
    protected $vehicle;

    public function setUp()
    {
        $this->motCurrent = XMock::of(
            '\DvsaEntities\Entity\MotTest',
            [
                'getVehicle',
                'getEmergencyLog',
                'getStartedDate'
            ]
        );
        $this->motPrevious = XMock::of('\DvsaEntities\Entity\MotTest', ['getExpiryDate']);
        $vehicleClass = new VehicleClass();
        $vehicleClass->setCode('4');

        $this->vehicle = new Vehicle();
        $this->vehicle->setVehicleClass($vehicleClass);
        $this->vehicle->setNewAtFirstReg(true);
        $this->vehicle->setFirstRegistrationDate(new \DateTime('1995-01-01'));

        $this->motCurrent->setVehicle($this->vehicle);

        $this->motCurrent->expects($this->any())
            ->method('getVehicle')
            ->willReturn($this->vehicle);
    }


    public function testNoPreviousOutsidePreservationDateNormalTestNotClass5()
    {
        $today = new \DateTime('2000-10-10');
        $motDate = new MotTestDate($today, $this->motCurrent, null);

        $e = $motDate->getExpiryDate();

        $this->assertEquals('2001-10-09', $e->format('Y-m-d'));
    }

    public function testNoPreviousOutsidePreservationDateNormalTestNotClass5NotRegAsNew()
    {
        $today = new \DateTime('2000-10-10');
        $motDate = new MotTestDate($today, $this->motCurrent, null);

        $this->vehicle->setNewAtFirstReg(false);
        $this->vehicle->setManufactureDate(new \DateTime('1995-01-01'));

        $e = $motDate->getExpiryDate();

        $this->assertEquals('2001-10-09', $e->format('Y-m-d'));
    }

    public function testNoPreviousOutsidePreservationDateEmergencyTestNotClass5()
    {
        $today = new \DateTime('2000-10-10');
        $motDate = new MotTestDate($today, $this->motCurrent, null);

        $this->motCurrent->expects($this->any())
            ->method('getEmergencyLog')
            ->willReturn(true);

        $this->motCurrent->expects($this->any())
            ->method('getStartedDate')
            ->willReturn($today);

        $e = $motDate->getExpiryDate();

        $this->assertEquals('2001-10-09', $e->format('Y-m-d'));
    }

    public function testNoPreviousOutsidePreservationDateNormalTestClass5()
    {
        $today = new \DateTime('2000-10-10');
        $motDate = new MotTestDate($today, $this->motCurrent, null);

        $vehicleClass = new VehicleClass();
        $vehicleClass->setCode('5');

        $this->vehicle->setVehicleClass($vehicleClass);
        $e = $motDate->getExpiryDate();
        $this->assertEquals('2001-10-09', $e->format('Y-m-d'));
    }

    public function testNoPreviousOutsidePreservationDateEmergencyTestClass5()
    {
        $today = new \DateTime('2000-10-10');
        $motDate = new MotTestDate($today, $this->motCurrent, null);

        $this->motCurrent->expects($this->any())
            ->method('getEmergencyLog')
            ->willReturn(true);

        $this->motCurrent->expects($this->any())
            ->method('getStartedDate')
            ->willReturn($today);

        $vehicleClass = new VehicleClass();
        $vehicleClass->setCode('5');

        $this->vehicle->setVehicleClass($vehicleClass);
        $e = $motDate->getExpiryDate();

        $this->assertEquals('2001-10-09', $e->format('Y-m-d'));
    }

    public function testNoPreviousInsidePreservationDateEmergencyTestClass5()
    {
        $today = new \DateTime('1995-12-10');
        $motDate = new MotTestDate($today, $this->motCurrent, null);

        $this->motCurrent->expects($this->any())
            ->method('getEmergencyLog')
            ->willReturn(true);

        $this->motCurrent->expects($this->any())
            ->method('getStartedDate')
            ->willReturn($today);

        $vehicleClass = new VehicleClass();
        $vehicleClass->setCode('5');

        $this->vehicle->setVehicleClass($vehicleClass);
        $e = $motDate->getExpiryDate();

        $this->assertEquals('1996-12-31', $e->format('Y-m-d'));
    }


    public function testNoPreviousInsidePreservationDateNormalTestNotClass5()
    {
        $today = new \DateTime('1997-12-10');
        $motDate = new MotTestDate($today, $this->motCurrent, null);

        $e = $motDate->getExpiryDate();

        $this->assertEquals('1998-12-31', $e->format('Y-m-d'));
    }

    /**
     * @expectedException \Exception
     */
    public function testNoPreviousInsidePreservationDateNormalTestNotClass5InvalidDate()
    {
        $today = new \DateTime('1995-12-10');
        $motDate = new MotTestDate($today, $this->motCurrent, null);
        $this->vehicle->setFirstRegistrationDate('not a date');
        $motDate->getExpiryDate();
    }

    /**
     * @expectedException \Exception
     */
    public function testNoPreviousInsidePreservationDateNormalTestNotClass5InvalidVehicleClassCode()
    {
        $today = new \DateTime('1995-12-10');
        $motDate = new MotTestDate($today, $this->motCurrent, null);

        $mockVehicleClass = XMock::of('DvsaEntities\Entity\VehicleClass', ['getCode']);
        $mockVehicleClass->expects($this->any())
            ->method('getCode')
            ->willReturn(null);

        $this->vehicle->setVehicleClass($mockVehicleClass);

        $motDate->getExpiryDate();
    }

    public function testWithPreviousOutsidePreservationDateNormalTestNotClass5NullIssuedDate()
    {
        $today = new \DateTime('2000-10-10');
        $motDate = new MotTestDate($today, $this->motCurrent, $this->motPrevious);

        $this->motCurrent->setIssuedDate(null);

        $e = $motDate->getExpiryDate();
        $this->assertEquals('2001-10-09', $e->format('Y-m-d'));
    }

    public function testWithPreviousOutsidePreservationDateContingencyTest()
    {
        $emergencyTest = XMock::of('\DvsaEntities\Entity\MotTest', []);

        $this->motCurrent->expects($this->any())
            ->method('getEmergencyLog')
            ->willReturn($emergencyTest);

        $today = new \DateTime('2000-10-10');

        $this->motCurrent->expects($this->any())
            ->method('getStartedDate')
            ->willReturn($today);

        $motDate = new MotTestDate($today, $this->motCurrent, $this->motPrevious);

        $e = $motDate->getExpiryDate();
        $this->assertEquals('2001-10-09', $e->format('Y-m-d'));
    }

    public function testStandardDurationPassesThroughNull()
    {
        $this->assertNull(MotTestDate::getStandardDurationExpiryDate(null));
    }

    public function testPreservationDatePassesThroughNull()
    {
        $this->assertNull(MotTestDate::preservationDate(null));
    }

    /**
     * @expectedException \Exception
     */
    public function testNotionalExpiryDateForNonDvlaVehicleHandlesNullVehicleClass()
    {
        $mockVehicle = XMock::of('DvsaEntities\Entity\Vehicle', ['getVehicleClass']);
        $mockVehicle->expects($this->once())
            ->method('getVehicleClass')
            ->willReturn(null);

        MotTestDate::getNotionalExpiryDateForVehicle($mockVehicle);
    }

    public function testNotionalExpiryReturnsNullForDvlaVehicle()
    {
        $this->assertNull(MotTestDate::getNotionalExpiryDateForVehicle(new DvlaVehicle()));
    }
}

