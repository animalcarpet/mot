<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot
 */

namespace DvsaMotApi\Factory\Helper;

use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaApplicationLogger\Log\Logger;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Helper\MysteryShopperHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class MysteryShopperHelperFactory.
 */
class MysteryShopperHelperFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return MysteryShopperHelper
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var FeatureToggles $featureToggles */
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');

        /** @var VehicleService $vehicleService */
        $vehicleService = $serviceLocator->get(VehicleService::class);

        /** @var MotAuthorisationServiceInterface $authorisationService */
        $authorisationService = $serviceLocator->get(MotAuthorisationServiceInterface::class);

        /** @var LoggerInterface $logger */
        $logger = $serviceLocator->get('Application\Logger');

        return new MysteryShopperHelper(
            $featureToggles,
            $vehicleService,
            $authorisationService,
            $logger
        );
    }
}
