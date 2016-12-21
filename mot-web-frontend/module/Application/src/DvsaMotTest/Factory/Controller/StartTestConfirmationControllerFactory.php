<?php

namespace DvsaMotTest\Factory\Controller;

use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaMotTest\Controller\StartTestConfirmationController;
use DvsaMotTest\Service\StartTestChangeService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Core\Catalog\CountryOfRegistration\CountryOfRegistrationCatalog;

/**
 * Create StartTestConfirmationController.
 */
class StartTestConfirmationControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return StartTestConfirmationController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /* @var ServiceLocatorInterface $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        $paramObfuscator = $serviceLocator->get(ParamObfuscator::class);
        $countryOfRegistrationCatalog = $serviceLocator->get(CountryOfRegistrationCatalog::class);
        $vehicleService = $serviceLocator->get(VehicleService::class);
        $startTestChangeService = $serviceLocator->get(StartTestChangeService::class);

        return new StartTestConfirmationController(
            $paramObfuscator,
            $countryOfRegistrationCatalog,
            $vehicleService,
            $startTestChangeService
        );
    }
}
