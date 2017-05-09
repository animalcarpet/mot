<?php

namespace Vehicle\UpdateVehicleProperty\ViewModel\Builder;

use Core\Routing\VehicleRoutes;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\View\Helper\Url;

class VehicleEditBreadcrumbsBuilder implements AutoWireableInterface
{
    /**
     * @var Url
     */
    private $url;

    public function __construct(Url $url)
    {
        $this->url = $url;
    }

    /**
     * @param string $stepTitle
     * @param string $obfucatedVehicleId
     *
     * @return array
     */
    public function getVehicleEditBreadcrumbs($stepTitle, $obfucatedVehicleId)
    {
        return [
            'Vehicle search' => VehicleRoutes::of($this->url)->vehicleSearch(),
            'Vehicle' => VehicleRoutes::of($this->url)->vehicleDetails($obfucatedVehicleId),
            $stepTitle => null,
        ];
    }

    public function getChangeVehicleUnderTestBreadcrumbs($obfucatedVehicleId)
    {
        return [
            'MOT testing' => '/vehicle-search',
            'Change vehicle record' => null,
        ];
    }
}
