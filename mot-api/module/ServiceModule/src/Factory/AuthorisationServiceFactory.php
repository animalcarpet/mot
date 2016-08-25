<?php

/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\ServiceModule\Factory;

use Dvsa\Mot\Api\ServiceModule\Model\ApiServicesConfigOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;

/**
 * Class VehicleServiceFactory
 */
class AuthorisationServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return AuthorisationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $tokenService = $serviceLocator->get('tokenService');
        $token = $tokenService->getToken();

        /** @var ApiServicesConfigOptions $configOptions */
        $configOptions = $serviceLocator->get(ApiServicesConfigOptions::class);
        $authorisationServiceUrl = $configOptions->getAuthorisationServiceUrl();

        $configOverride = isset($authorisationServiceUrl) ?
            ['http_client' => ['base_uri' => $authorisationServiceUrl]] : null;

        return new AuthorisationService($token, $configOverride);
    }
}
