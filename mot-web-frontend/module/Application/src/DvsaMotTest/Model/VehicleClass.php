<?php

namespace DvsaMotTest\Model;

use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Enum\VehicleClassCode;

/**
 * Class VehicleClass.
 */
class VehicleClass
{
    private $code;
    private $id;
    private $name;

    public function __construct(VehicleClassDto $vehicleClass)
    {
        $this->code = $vehicleClass->getCode();
        $this->id = $vehicleClass->getId();
        $this->name = $vehicleClass->getName();
    }

    public static function getVehicleClassesCodes()
    {
        return [
            VehicleClassCode::CLASS_1 => VehicleClassCode::CLASS_1,
            VehicleClassCode::CLASS_2 => VehicleClassCode::CLASS_2,
            VehicleClassCode::CLASS_3 => VehicleClassCode::CLASS_3,
            VehicleClassCode::CLASS_4 => VehicleClassCode::CLASS_4,
            VehicleClassCode::CLASS_5 => VehicleClassCode::CLASS_5,
            VehicleClassCode::CLASS_7 => VehicleClassCode::CLASS_7,
        ];
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
