<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot
 */

namespace DvsaMotApi\Helper;

use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaApplicationLogger\Log\Logger;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaFeature\FeatureToggles;
use InvalidArgumentException;
use Zend\Log\LoggerInterface;

/**
 * Helper class for determining if a vehicle or MOT test should be classified as Mystery Shopper.
 */
class MysteryShopperHelper
{
    /** @var FeatureToggles */
    private $featureToggles;

    /** @var VehicleService */
    private $vehicleService;

    /** @var MotAuthorisationServiceInterface */
    protected $authorisationService;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param FeatureToggles $featureToggles
     * @param VehicleService $vehicleService
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param LoggerInterface $logger
     */
    public function __construct(
        FeatureToggles $featureToggles,
        VehicleService $vehicleService,
        MotAuthorisationServiceInterface $authorisationService,
        LoggerInterface $logger)
    {
        $this->featureToggles = $featureToggles;
        $this->vehicleService = $vehicleService;
        $this->authorisationService = $authorisationService;
        $this->logger = $logger;
    }

    /**
     * @param int $vehicleId
     *
     * @return bool
     */
    public function isVehicleMysteryShopper($vehicleId)
    {
        if (null === $vehicleId) {
            $this->logger->err('Could not get vehicle details on vehicle id: ' . $vehicleId);
            return false;
        }
        return $this->isMysteryShopperToggleEnabled()
            && (true === $this->vehicleService->getDvsaVehicleById((int) $vehicleId)->getIsIncognito());
    }

    /**
     * @return bool
     */
    public function hasPermissionToMaskAndUnmaskVehicles()
    {
        return $this->isMysteryShopperToggleEnabled()
            && (true === $this->authorisationService->isGranted(PermissionInSystem::ENFORCEMENT_CAN_MASK_AND_UNMASK_VEHICLES));
    }

    /**
     * @return bool
     */
    public function hasPermissionToViewMysteryShopperTests()
    {
        return $this->isMysteryShopperToggleEnabled()
            && (true === $this->authorisationService->isGranted(PermissionInSystem::VIEW_MYSTERY_SHOPPER_TESTS));
    }

    /**
     * @return bool
     */
    public function isMysteryShopperToggleEnabled()
    {
        return (true === $this->featureToggles->isEnabled(FeatureToggle::MYSTERY_SHOPPER));
    }
}
