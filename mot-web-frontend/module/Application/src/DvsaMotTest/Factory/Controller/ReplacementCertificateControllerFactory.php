<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotTest\Factory\Controller;

use DvsaMotTest\Controller\ReplacementCertificateController;
use DvsaMotTest\Model\OdometerReadingViewObject;
use DvsaMotTest\Service\ReplacementCertificateDraftService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Vehicle\Service\VehicleCatalogService;

/**
 * Create ReplacementCertificateController.
 */
class ReplacementCertificateControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     * @return ReplacementCertificateController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        $replacementCertificateDraftService = $serviceLocator->get(ReplacementCertificateDraftService::class);
        $vehicleCatalogService = $serviceLocator->get(VehicleCatalogService::class);
        $odometerViewObject = new OdometerReadingViewObject();

        return new ReplacementCertificateController(
            $replacementCertificateDraftService,
            $vehicleCatalogService,
            $odometerViewObject
        );
    }
}
