<?php

namespace Vehicle\Helper;

use Core\ViewModel\Header\HeaderTertiaryList;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class VehiclePageTitleBuilder implements AutoWireableInterface
{
    /**
     * @var DvsaVehicle
     */
    private $vehicle;

    /**
     * @param DvsaVehicle $vehicle
     * @return VehiclePageTitleBuilder
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
            ? $this->vehicle->getMake()->getName() . ', ' . $this->vehicle->getModel()->getName()
            : $this->vehicle->getMake()->getName();
    }

    /**
     * @return HeaderTertiaryList
     */
    public function getPageTertiaryTitle()
    {
        $header = new HeaderTertiaryList();
        $header->addElement($this->vehicle->getRegistration());
        $header->addElement($this->vehicle->getVin());

        return $header;
    }
}