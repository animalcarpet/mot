<?php

namespace Dvsa\Mot\Frontend\ServiceModule\Factory;

use Dvsa\Mot\ApiClient\Service\MotTestService;
use DvsaAuthentication\Service\ApiTokenService;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\FactoryInterface;
use Dvsa\Mot\Frontend\ServiceModule\Model\ApiServicesConfigOptions;

class MotTestServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ApiTokenService $tokenService */
        $tokenService = $serviceLocator->get('tokenService');
        $token = $tokenService->getToken();

        /** @var ApiServicesConfigOptions $configOptions */
        $configOptions = $serviceLocator->get(ApiServicesConfigOptions::class);
        $motTestServiceUrl = $configOptions->getMotTestServiceUrl();

        $configOverride = isset($motTestServiceUrl) ?
            ['http_client' => ['base_uri' => $motTestServiceUrl]] : null;

        return new MotTestService($token, $configOverride);
    }


}