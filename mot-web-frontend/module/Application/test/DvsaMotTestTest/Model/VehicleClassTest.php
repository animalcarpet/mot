<?php

namespace DvsaMotTest\Model;

use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\VehicleClassCode;
use PHPUnit_Framework_TestCase;

/**
 * Class VehicleClassTest.
 */
class VehicleClassTest extends PHPUnit_Framework_TestCase
{
    public function testVehicleClassCreationAndGetters()
    {
        //given
        $id = 2;
        $code = 3;
        $name = 4;
        //when
        $vehicleClass = new VehicleClass(
            (new VehicleClassDto())
                ->setId($id)
                ->setCode($code)
                ->setName($name)
        );
        //then
        $this->assertEquals($id, $vehicleClass->getId());
        $this->assertEquals($code, $vehicleClass->getCode());
        $this->assertEquals($name, $vehicleClass->getName());
    }

    public function testGetVehicleClasses()
    {
        $this->assertEquals(
            [
                VehicleClassCode::CLASS_1 => VehicleClassCode::CLASS_1,
                VehicleClassCode::CLASS_2 => VehicleClassCode::CLASS_2,
                VehicleClassCode::CLASS_3 => VehicleClassCode::CLASS_3,
                VehicleClassCode::CLASS_4 => VehicleClassCode::CLASS_4,
                VehicleClassCode::CLASS_5 => VehicleClassCode::CLASS_5,
                VehicleClassCode::CLASS_7 => VehicleClassCode::CLASS_7,
            ],
            VehicleClass::getVehicleClassesCodes()
        );
    }
}
