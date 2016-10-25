<?php

namespace Vehicle\Helper;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaFeature\FeatureToggles;
use Vehicle\Controller\VehicleController;
use Vehicle\ViewModel\Sidebar\VehicleSidebar;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Helper\Url;

class VehicleSidebarBuilder implements AutoWireableInterface
{
    /**
     * @var Url
     */
    private $url;

    /**
     * @var ParametersInterface
     */
    private $searchData;

    /** @var string
     */
    private $obfuscatedVehicleId;

    /**
     * @var bool
     */
    private $isVehicleMasked;

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var FeatureToggles
     */
    private $featureToggles;

    /**
     * VehicleSidebarBuilder constructor.
     *
     * @param Url $url
     */
    public function __construct(Url $url, MotAuthorisationServiceInterface $authorisationService,
                                FeatureToggles $featureToggles)
    {
        $this->url = $url;
        $this->isVehicleMasked = false;
        $this->authorisationService = $authorisationService;
        $this->featureToggles = $featureToggles;
    }

    /**
     * @param ParametersInterface $searchData
     *
     * @return VehicleSidebarBuilder
     */
    public function setSearchData($searchData)
    {
        $this->searchData = $searchData;

        return $this;
    }

    /**
     * @param string $obfuscatedVehicleId
     *
     * @return VehicleSidebarBuilder
     */
    public function setObfuscatedVehicleId($obfuscatedVehicleId)
    {
        $this->obfuscatedVehicleId = $obfuscatedVehicleId;

        return $this;
    }

    /**
     * @return $this
     */
    public function setVehicleAsMasked()
    {
        $this->isVehicleMasked = true;

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

        return new VehicleSidebar($this->url, $searchData, $this->obfuscatedVehicleId, $this->isVehicleMasked,
            $this->authorisationService, $this->featureToggles);
    }
}
