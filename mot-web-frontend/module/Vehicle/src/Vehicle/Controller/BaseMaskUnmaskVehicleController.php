<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Vehicle\Controller;

use Core\Controller\AbstractAuthActionController;
use Core\Routing\VehicleRoutes;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaFeature\FeatureToggles;
use Zend\View\Helper\Url;
use Zend\View\Model\ViewModel;

/**
 * Base class for Mask and Unmask controllers.
 */
abstract class BaseMaskUnmaskVehicleController extends AbstractAuthActionController implements AutoWireableInterface
{
    /**
     * @var ParamObfuscator
     */
    protected $paramObfuscator;

    /**
     * @var MotAuthorisationServiceInterface
     */
    protected $authorisationService;

    /**
     * @var VehicleService
     */
    protected $vehicleService;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var FeatureToggles
     */
    protected $featureToggles;

    /**
     * MaskVehicleController constructor.
     *
     * @param ParamObfuscator                  $paramObfuscator
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param VehicleService                   $vehicleService
     * @param Url                              $url
     * @param \DvsaFeature\FeatureToggles      $featureToggles
     */
    public function __construct(ParamObfuscator $paramObfuscator, MotAuthorisationServiceInterface $authorisationService,
                                VehicleService $vehicleService, Url $url, FeatureToggles $featureToggles)
    {
        $this->paramObfuscator = $paramObfuscator;
        $this->authorisationService = $authorisationService;
        $this->url = $url;
        $this->featureToggles = $featureToggles;
        $this->vehicleService = $vehicleService;
    }

    /**
     * @param string $template
     * @param array  $variables
     *
     * @return ViewModel
     */
    protected function createViewModel($template, array $variables)
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate($template);
        $viewModel->setVariables($variables);

        return $viewModel;
    }

    /**
     * @param string $obfuscatedVehicleId
     * @param string $leafTitle
     *
     * @return array
     */
    protected function getBreadcrumbs($obfuscatedVehicleId, $leafTitle)
    {
        return [
            'Vehicle' => VehicleRoutes::of($this->url)->vehicleDetails($obfuscatedVehicleId),
            $leafTitle => '',
        ];
    }

    /**
     * @param string $obfuscatedVehicleId
     *
     * @return int
     */
    protected function deobfuscateVehicleId($obfuscatedVehicleId)
    {
        return (int) $this->paramObfuscator->deobfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $obfuscatedVehicleId);
    }
}
