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
     * @param array $searchParams
     * @return array
     */
    public function getVehicleEditBreadcrumbs($stepTitle, $obfucatedVehicleId, $searchParams = [])
    {
        return [
            'Vehicle' => VehicleRoutes::of($this->url)->vehicleDetails($obfucatedVehicleId, $searchParams),
            $stepTitle => null,
        ];
    }
}