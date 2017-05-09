<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Model;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation;
use PHPUnit_Framework_TestCase;

/**
 * Class VehicleTestingStationTest.
 */
class VehicleTestingStationTest extends PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $data = $this->getVtsTestData();
        $vts = new VehicleTestingStation($data);
        $this->assertEquals($data['id'], $vts->getVtsId());
        $this->assertEquals($data['name'], $vts->getName());
        $this->assertEquals($data['address'], $vts->getAddress());
        $this->assertEquals($data['siteNumber'], $vts->getSiteNumber());
        $this->assertEquals($data['slots'], $vts->getSlots());
        $this->assertEquals($data['slotsWarning'], $vts->getSlotsWarning());
        $this->assertEquals($data['aeId'], $vts->getAuthorisedExaminerId());
        $this->assertEquals($data['slotsInUse'], $vts->getSlotsInUse());
    }

    public function testFluentInterface()
    {
        $data = $this->getVtsTestData();
        $vts = new VehicleTestingStation();
        $retobj = $vts->setVtsId($data['id'])
            ->setAddress($data['address'])
            ->setAuthorisedExaminerId($data['aeId'])
            ->setName($data['name'])
            ->setSiteNumber($data['siteNumber'])
            ->setSlots($data['slots'])
            ->setSlotsWarning($data['slotsWarning'])
            ->setSlotsInUse($data['slotsInUse']);

        $this->assertInstanceOf(\Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation::class, $retobj);
        $this->assertEquals($data['id'], $vts->getVtsId());
        $this->assertEquals($data['name'], $vts->getName());
        $this->assertEquals($data['address'], $vts->getAddress());
        $this->assertEquals($data['siteNumber'], $vts->getSiteNumber());
        $this->assertEquals($data['slots'], $vts->getSlots());
        $this->assertEquals($data['slotsWarning'], $vts->getSlotsWarning());
        $this->assertEquals($data['aeId'], $vts->getAuthorisedExaminerId());
        $this->assertEquals($data['slotsInUse'], $vts->getSlotsInUse());
    }

    protected function getVtsTestData()
    {
        $data = [
            'id' => 1,
            'name' => 'Test VTS Station',
            'address' => 'No 1 My Street, My Town, Metropolis',
            'siteNumber' => 'V1234567',
            'slots' => 99,
            'slotsWarning' => 10,
            'slotsInUse' => 9,
            'aeId' => 1,
        ];

        return $data;
    }
}
