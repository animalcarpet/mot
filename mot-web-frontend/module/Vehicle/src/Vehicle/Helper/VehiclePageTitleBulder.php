<?php

namespace Vehicle\Helper;

use Core\ViewModel\Header\HeaderTertiaryList;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class VehiclePageTitleBulder implements AutoWireableInterface
{
    /**
     * @var DvsaVehicle
     */
    private $vehicle;

    /**
     * @param DvsaVehicle $vehicle
     * @return VehiclePageTitleBulder
     */
    public function setVehicle($vehicle)
    {
        $this->vehicle = $vehicle;
        return $this;
    }

    public function getPageSecondaryTitle()
    {
        return 'Vehicle';
    }

    public function getPageTitle()
    {
        return $this->getMakeAndModel();
    }

    /**
     * @return string
     */
    private function getMakeAndModel()
    {
        return $this->vehicle->getModel()
            ? $this->vehicle->getMake() . ', ' . $this->vehicle->getModel()
            : $this->vehicle->getMake();
    }

    /**
     * @return HeaderTertiaryList
     */
    public function getPageTertiaryTitle()
    {
        $header = new HeaderTertiaryList();
        $header->addRow($this->vehicle->getRegistration());
        $header->addRow($this->vehicle->getVin());

        return $header;
    }
}