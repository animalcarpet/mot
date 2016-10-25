<?php

namespace Vehicle\Helper;

use Core\Routing\VehicleRoutes;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle as Vehicle;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Dto\Vehicle\VehicleExpiryDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaFeature\FeatureToggles;
use UnexpectedValueException;
use Vehicle\Controller\VehicleController;
use Vehicle\ViewModel\VehicleViewModel;
use Zend\Stdlib\ParametersInterface;
use Zend\View\Helper\Url;

class VehicleViewModelBuilder implements AutoWireableInterface
{
    private $url;
    private $vehicleTableBuilder;
    private $vehiclePageTitleBulder;
    private $vehicleSidebarBuilder;

    /**
     * @var Vehicle
     */
    private $vehicle;

    /**
     * @var VehicleExpiryDto
     */
    private $expiryDateInformation;

    /**
     * @var string
     */
    private $obfuscatedVehicleId;

    /**
     * @var ParametersInterface
     */
    private $searchData;

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var FeatureToggles
     */
    private $featureToggles;

    /**
     * VehicleViewModelBuilder constructor.
     *
     * @param Url                            $url
     * @param VehicleInformationTableBuilder $vehicleTableBuilder
     * @param VehiclePageTitleBuilder        $vehiclePageTitleBulder
     * @param VehicleSidebarBuilder          $vehicleSidebarBuilder
     * @param FeatureToggles                 $featureToggles
     */
    public function __construct(Url $url, VehicleInformationTableBuilder $vehicleTableBuilder,
                                VehiclePageTitleBuilder $vehiclePageTitleBulder,
                                VehicleSidebarBuilder $vehicleSidebarBuilder,
                                MotAuthorisationServiceInterface $authorisationService, FeatureToggles $featureToggles)
    {
        $this->url = $url;
        $this->vehicleTableBuilder = $vehicleTableBuilder;
        $this->vehiclePageTitleBulder = $vehiclePageTitleBulder;
        $this->vehicleSidebarBuilder = $vehicleSidebarBuilder;
        $this->authorisationService = $authorisationService;
        $this->featureToggles = $featureToggles;
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return VehicleViewModelBuilder
     */
    public function setVehicle(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    /**
     * @param string $obfuscatedVehicleId
     *
     * @return VehicleViewModelBuilder
     */
    public function setObfuscatedVehicleId($obfuscatedVehicleId)
    {
        $this->obfuscatedVehicleId = $obfuscatedVehicleId;

        return $this;
    }

    /**
     * @param ParametersInterface $searchData
     *
     * @return VehicleViewModelBuilder
     */
    public function setSearchData($searchData)
    {
        $this->searchData = $searchData;

        return $this;
    }

    /**
     * @param VehicleExpiryDto $expiryDateInformation
     *
     * @return VehicleViewModelBuilder
     */
    public function setExpiryDateInformation($expiryDateInformation)
    {
        $this->expiryDateInformation = $expiryDateInformation;

        return $this;
    }

    /**
     * @return VehicleViewModel
     */
    public function getViewModel()
    {
        if (!$this->vehicle instanceof Vehicle) {
            throw new UnexpectedValueException(sprintf('Method "%s::setVehicle()" must be called before executing "%s()',
                static::class, __METHOD__));
        }

        $this->vehicleTableBuilder->setExpiryDateInformation($this->expiryDateInformation);
        $this->vehicleTableBuilder->setVehicle($this->vehicle);
        $this->vehicleTableBuilder->setVehicleObfuscatedId($this->obfuscatedVehicleId);
        $this->vehiclePageTitleBulder->setVehicle($this->vehicle);

        $this->vehicleSidebarBuilder->setSearchData($this->searchData);
        $this->vehicleSidebarBuilder->setObfuscatedVehicleId($this->obfuscatedVehicleId);
        if ($this->vehicle->getIsIncognito()) {
            $this->vehicleSidebarBuilder->setVehicleAsMasked();
        }

        $shouldDisplayVehicleMaskedBanner = (true === $this->featureToggles->isEnabled(FeatureToggle::MYSTERY_SHOPPER))
            && $this->authorisationService->isGranted(PermissionInSystem::ENFORCEMENT_CAN_MASK_AND_UNMASK_VEHICLES)
            && $this->vehicle->getIsIncognito();

        return (new VehicleViewModel())
            ->setVehicleSpecificationGdsTable($this->vehicleTableBuilder->getVehicleSpecificationGdsTable())
            ->setVehicleRegistrationGdsTable($this->vehicleTableBuilder->getVehicleRegistrationGdsTable())
            ->setPageTitle($this->vehiclePageTitleBulder->getPageTitle())
            ->setPageSecondaryTitle($this->vehiclePageTitleBulder->getPageSecondaryTitle())
            ->setPageTertiaryTitle($this->vehiclePageTitleBulder->getPageTertiaryTitle())
            ->setSidebar($this->vehicleSidebarBuilder->getSidebar())
            ->setBackUrl($this->getUrlToBack())
            ->setBreadcrumbs($this->getBreadcrumbs())
            ->setBackLinkText($this->getBackLinkText())
            ->setShouldDisplayVehicleMaskedBanner($shouldDisplayVehicleMaskedBanner)
            ->setObfuscatedVehicleId($this->obfuscatedVehicleId);
    }

    /**
     * @return string
     */
    private function getUrlToBack()
    {
        $searchData = clone $this->searchData;
        $searchData->set(VehicleController::PARAM_BACK_TO, null);

        if ($this->shouldGoBackToSearch()) {
            return VehicleRoutes::of($this->url)->vehicleSearch();
        } else {
            return VehicleRoutes::of($this->url)->vehicleSearchResults($searchData->toArray());
        }
    }

    /**
     * @return bool
     */
    private function shouldGoBackToSearch()
    {
        $backToParamValue = $this->searchData->get(VehicleController::PARAM_BACK_TO);

        return $this->searchData->count() === 0
            || $backToParamValue == VehicleController::BACK_TO_SEARCH
            || $backToParamValue != VehicleController::BACK_TO_RESULT;
    }

    /**
     * @return string
     */
    private function getBackLinkText()
    {
        if ($this->shouldGoBackToSearch()) {
            return 'Return to vehicle information search';
        }

        return 'Return to vehicle search results';
    }

    /**
     * @return array
     */
    private function getBreadcrumbs()
    {
        return [
            'breadcrumbs' => [
                'Vehicle search' => VehicleRoutes::of($this->url)->vehicleSearch(),
                'Vehicle' => null,
            ],
        ];
    }
}
