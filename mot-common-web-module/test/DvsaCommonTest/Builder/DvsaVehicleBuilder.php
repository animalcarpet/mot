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
        $vehicle->registration = null;
        $vehicle->vin = null;
        $vehicle->colour = null;
        $vehicle->colourSecondary = null;
        $vehicle->version = 10000;
        $vehicle->firstUsedDate = "2017-01-10";
        $vehicle->weightSource = null;
        return $vehicle;
    }
}