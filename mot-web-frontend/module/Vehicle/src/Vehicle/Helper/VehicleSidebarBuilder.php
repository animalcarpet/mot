<?php

namespace Vehicle\Helper;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Vehicle\Controller\VehicleController;
use Vehicle\ViewModel\Sidebar\VehicleSidebar;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Helper\Url;

class VehicleSidebarBuilder implements AutoWireableInterface
{
    private $url;
    /** @var  ParametersInterface */
    private $searchData;
    /** @var  string */
    private $obfuscatedVehicleId;

    public function __construct(Url $url)
    {
        $this->url = $url;
    }

    /**
     * @param ParametersInterface $searchData
     * @return VehicleSidebarBuilder
     */
    public function setSearchData($searchData)
    {
        $this->searchData = $searchData;
        return $this;
    }

    /**
     * @param string $obfuscatedVehicleId
     * @return VehicleSidebarBuilder
     */
    public function setObfuscatedVehicleId($obfuscatedVehicleId)
    {
        $this->obfuscatedVehicleId = $obfuscatedVehicleId;
        return $this;
    }

    /**
     * @return VehicleSidebar
     */
    public function getSidebar()
    {
        $searchData = clone $this->searchData;
        $backTo = $searchData->get(VehicleController::PARAM_BACK_TO);

        if ($backTo == VehicleController::BACK_TO_SEARCH) {
            $searchData->set(VehicleController::SEARCH_RETUREND_ONE_RESULT, true);
        }

        $searchData->set(VehicleController::PARAM_BACK_TO, VehicleController::BACK_TO_DETAIL);

        return new VehicleSidebar($this->url, $searchData, $this->obfuscatedVehicleId);
    }
}