<?php

namespace DvsaMotTest\Factory\Controller;

use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaMotTest\Controller\RefuseToTestController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create RefuseToTestController.
 */
class RefuseToTestControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return RefuseToTestController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        $paramObfuscator = $serviceLocator->get(ParamObfuscator::class);
        $vehicleService = $serviceLocator->get(VehicleService::class);
        $restClient = $serviceLocator->get(Client::class);
        $authorisationService = $serviceLocator->get('AuthorisationService');
        $catalogService = $serviceLocator->get('CatalogService');
        $session = $serviceLocator->get('MotSession');
        $identityProvider = $serviceLocator->get('MotIdentityProvider');

        return new RefuseToTestController(
            $paramObfuscator,
            $restClient,
            $vehicleService,
            $authorisationService,
            $catalogService,
            $session,
            $identityProvider
        );
    }
}
