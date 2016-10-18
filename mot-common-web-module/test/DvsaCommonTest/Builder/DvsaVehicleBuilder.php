<?php

namespace DvsaCommonTest\Builder;

class DvsaVehicleBuilder
{
    public function getEmptyVehicleStdClass()
    {
        $vehicle = new \stdClass();
        $emptyResource = new \stdClass();
        $emptyResource->name = null;
        $emptyResource->id = null;
        $vehicle->make = $emptyResource;
        $vehicle->model = $emptyResource;
        $vehicle->vehicleClass = null;
        $vehicle->fuelType = $emptyResource;

        return $vehicle;
    }
}