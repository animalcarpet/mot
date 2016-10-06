<?php

namespace Core\Routing;

use Zend\Mvc\Controller\AbstractController;
use Zend\View\Helper\Url;
use Zend\View\Renderer\PhpRenderer;

class VehicleRoutes extends AbstractRoutes
{
    /**
     * @param Url|PhpRenderer|AbstractController|\Zend\Mvc\Controller\Plugin\Url $object
     * @return VehicleRoutes
     */
    public static function of($object)
    {
        return new VehicleRoutes($object);
    }

    public function vehicleMotTestHistory($obfuscatedVehicleId, $searchParams)
    {
        return $this->url(
            VehicleRouteList::VEHICLE_MOT_HISTORY,
            [
                'id' => $obfuscatedVehicleId,
            ],
            [
                'query' => $searchParams,
            ]
        );
    }

    public function vehicleSearch($searchParams)
    {
        return $this->url(
            VehicleRouteList::VEHICLE_SEARCH,
            [],
            [
                'query' => $searchParams,
            ]
        );
    }

    public function vehicleSearchResults($searchParams)
    {
        return $this->url(
            VehicleRouteList::VEHICLE_SEARCH_RESULTS,
            [],
            [
                'query' => $searchParams,
            ]
        );
    }

    public function vehicleDetails($vehicleId, $searchParams)
    {
        return $this->url(
            VehicleRouteList::VEHICLE_DETAIL,
            [
                'id' => $vehicleId,
            ],
            [
                'query' => $searchParams,
            ]
        );
    }

    public function vehicleEditEngine($vehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_CHANGE_ENGINE, ['id' => $vehicleId]);
    }

    public function changeClass($vehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_CHANGE_CLASS,['id' => $vehicleId]);
    }
}
