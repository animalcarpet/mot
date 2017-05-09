<?php

/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\ServiceModule\Factory;

use DvsaAuthentication\Service\ApiTokenService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\Api\ServiceModule\Model\ApiServicesConfigOptions;

/**
 * Class VehicleServiceFactory.
 */
class VehicleServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
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

        $configOverride = isset($vehicleServiceUrl) ? ['http_client' => ['base_uri' => $vehicleServiceUrl]] : null;

        $vehicleService = new VehicleService($token, $configOverride);

        return $vehicleService;
    }
}
