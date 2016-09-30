<?php
namespace Vehicle\UpdateVehicleProperty\ViewModel\Builder;

use Core\ViewModel\Header\HeaderTertiaryList;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class VehicleTertiaryTitleBuilder implements AutoWireableInterface
{

    /**
     * @param DvsaVehicle $vehicle
     * @return HeaderTertiaryList
     */
    public function getTertiaryTitleForVehicle(DvsaVehicle $vehicle)
    {
        $title = new HeaderTertiaryList();
        $title->addElement($vehicle->getMake() . ', ' . $vehicle->getModel())->bold();
        $title->addElement($vehicle->getRegistration());
        $title->addElement($vehicle->getVin());

        return $title;
    }
}