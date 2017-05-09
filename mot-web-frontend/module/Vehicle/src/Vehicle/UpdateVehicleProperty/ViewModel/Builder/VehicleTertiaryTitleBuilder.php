<?php

namespace Vehicle\UpdateVehicleProperty\ViewModel\Builder;

use Core\ViewModel\Header\HeaderTertiaryList;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class VehicleTertiaryTitleBuilder implements AutoWireableInterface
{
    /**
     * @param DvsaVehicle $vehicle
     *
     * @return HeaderTertiaryList
     */
    public function getTertiaryTitleForVehicle(DvsaVehicle $vehicle)
    {
        $title = new HeaderTertiaryList();
        $title->addElement($this->getMakeAndModel($vehicle))->bold();
        $title->addElement($vehicle->getRegistration());
        $title->addElement($vehicle->getVin());

        return $title;
    }

    private function getMakeName(DvsaVehicle $vehicle)
    {
        if ($this->hasMake($vehicle)) {
            return $vehicle->getMake()->getName();
        } else {
            return '';
        }
    }

    private function hasMake(DvsaVehicle $vehicle)
    {
        return $vehicle->getMake() !== null;
    }

    private function getModelName(DvsaVehicle $vehicle)
    {
        if ($this->hasModel($vehicle)) {
            return $vehicle->getModel()->getName();
        } else {
            return '';
        }
    }

    private function hasModel(DvsaVehicle $vehicle)
    {
        return $vehicle->getModel() !== null;
    }

    private function getMakeAndModel(DvsaVehicle $vehicle)
    {
        $elements = [];
        if ($this->hasMake($vehicle)) {
            $elements[] = $this->getMakeName($vehicle);
        }

        if ($this->hasModel($vehicle)) {
            $elements[] = $this->getModelName($vehicle);
        }

        return implode(', ', $elements);
    }
}
