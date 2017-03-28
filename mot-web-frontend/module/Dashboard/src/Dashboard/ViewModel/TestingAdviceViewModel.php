<?php

namespace Dashboard\ViewModel;

use Core\Routing\VehicleRouteList;

class TestingAdviceViewModel
{
    private $isTestingAdviceAvailable;
    private $vehicleId;
    private $motTestNumber;

    public function __construct($isTestingAdviceAvailable, $vehicleId, $motTestNumber)
    {
        $this->isTestingAdviceAvailable = $isTestingAdviceAvailable;
        $this->vehicleId = $vehicleId;
        $this->motTestNumber = $motTestNumber;
    }

    public function getIsTestingAdviceAvailable()
    {
        return $this->isTestingAdviceAvailable;
    }

    public function getVehicleId()
    {
        return $this->vehicleId;
    }

    public function getRoute()
    {
        return VehicleRouteList::VEHICLE_TESTING_ADVICE;
    }

    public function getRouteParameters()
    {
        return ['id' => $this->vehicleId];
    }

    public function getRouteOptions()
    {
        return ['query' => [
            'navigateFrom' => 'home-page',
            'motTestNumber' => $this->motTestNumber,
        ], ];
    }
}
