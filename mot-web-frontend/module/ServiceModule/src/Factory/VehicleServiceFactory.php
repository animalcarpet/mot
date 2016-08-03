<?php

/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\ServiceModule\Factory;

use DvsaAuthentication\Service\ApiTokenService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Frontend\ServiceModule\Model\ApiServicesConfigOptions;

/**
 * Class VehicleServiceFactory
 */
class VehicleServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return VehicleService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ApiTokenService $tokenService */
        $tokenService = $serviceLocator->get('tokenService');
        $token = $tokenService->getToken();

        /** @var ApiServicesConfigOptions $configOptions */
        $configOptions = $serviceLocator->get(ApiServicesConfigOptions::class);
        $vehicleServiceUrl = $configOptions->getVehicleServiceUrl();

        $configOverride = isset($vehicleServiceUrl) ? ['http_client' => ['base_uri' =>  $vehicleServiceUrl]] : null;

        return new VehicleService($token, $configOverride);
    }
}
