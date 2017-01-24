<?php

namespace Core\Routing;

use Vehicle\UpdateVehicleProperty\Form\Wizard\Step\UpdateMakeStep;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Step\UpdateModelStep;
use Zend\Mvc\Controller\AbstractController;
use Zend\View\Helper\Url;
use Zend\View\Renderer\PhpRenderer;

class VehicleRoutes extends AbstractRoutes
{
    /**
     * @param Url|PhpRenderer|AbstractController|\Zend\Mvc\Controller\Plugin\Url $object
     *
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

    public function vehicleSearch()
    {
        return $this->url(VehicleRouteList::VEHICLE_SEARCH);
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

    public function vehicleDetails($vehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_DETAIL, ['id' => $vehicleId]);
    }

    public function vehicleEditEngine($vehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_CHANGE_ENGINE, ['id' => $vehicleId]);
    }

    public function changeClass($vehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_CHANGE_CLASS, ['id' => $vehicleId]);
    }

    /**
     * @param $vehicleId
     *
     * @return mixed
     */
    public function changeUnderTestClass($vehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_CHANGE_UNDER_TEST_CLASS, ['id' => $vehicleId]);
    }

    /**
     * @param $vehicleId
     *
     * @return mixed
     */
    public function changeUnderTestEngine($vehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_CHANGE_UNDER_TEST_ENGINE, ['id' => $vehicleId]);
    }

    /**
     * @param $vehicleId
     *
     * @return mixed
     */
    public function changeUnderTestColour($vehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_CHANGE_UNDER_TEST_COLOUR, ['id' => $vehicleId]);
    }

    public function changeFirstUsedDate($vehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_CHANGE_FIRST_USED_DATE, ['id' => $vehicleId]);
    }

    public function changeMake($obfuscatedVehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_CHANGE_MAKE_AND_MODEL, ['id' => $obfuscatedVehicleId, "property" => UpdateMakeStep::NAME]);
    }

    public function changeModel($obfuscatedVehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_CHANGE_MAKE_AND_MODEL, ['id' => $obfuscatedVehicleId, "property" => UpdateModelStep::NAME]);
    }

    /**
     * @param $obfuscatedVehicleId
     *
     * @return mixed
     */
    public function changeUnderTestCountryOfRegistration($obfuscatedVehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_CHANGE_UNDER_TEST_COUNTRY_OF_REGISTRATION, ['id' => $obfuscatedVehicleId]);
    }

    /**
     * @param $obfuscatedVehicleId
     *
     * @return mixed
     */
    public function changeCountryOfRegistration($obfuscatedVehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_CHANGE_COUNTRY_OF_REGISTRATION, ['id' => $obfuscatedVehicleId]);
    }

    /**
     * @param string $obfuscatedVehicleId
     *
     * @return string
     */
    public function maskVehicle($obfuscatedVehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_ENFORCEMENT_MASK, ['id' => $obfuscatedVehicleId]);
    }

    /**
     * @param string $obfuscatedVehicleId
     *
     * @return string
     */
    public function vehicleMaskedSuccessfully($obfuscatedVehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_ENFORCEMENT_MASKED_SUCCESSFULLY, ['id' => $obfuscatedVehicleId]);
    }

    /**
     * @param string $obfuscatedVehicleId
     *
     * @return string
     */
    public function unmaskVehicle($obfuscatedVehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_ENFORCEMENT_UNMASK, ['id' => $obfuscatedVehicleId]);
    }

    /**
     * @param string $obfuscatedVehicleId
     *
     * @return string
     */
    public function vehicleUnmaskedSuccessfully($obfuscatedVehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_ENFORCEMENT_UNMASKED_SUCCESSFULLY, ['id' => $obfuscatedVehicleId]);
    }

    public function changeColour($obfuscatedVehicleId)
    {
        return $this->url(VehicleRouteList::VEHICLE_CHANGE_COLOUR,['id' => $obfuscatedVehicleId, "property" => UpdateModelStep::NAME]);
    }
}
