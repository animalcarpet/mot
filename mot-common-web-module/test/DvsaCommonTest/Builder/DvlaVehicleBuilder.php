<?php

namespace DvsaCommonTest\Builder;

class DvlaVehicleBuilder
{
    public function getEmptyVehicleStdClass()
    {
        $vehicle = new \stdClass();
        $emptyResource = new \stdClass();
        $emptyResource->name = null;
        $emptyResource->id = null;
        $vehicle->make = $emptyResource;
        $vehicle->model = $emptyResource;
        $vehicle->fuelType = $emptyResource;
        $vehicle->registration = null;
        $vehicle->vin = null;
        $vehicle->colour = null;
        $vehicle->colourSecondary = null;

        return $vehicle;
    }
}