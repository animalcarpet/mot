<?php

namespace DvsaMotTest\Factory\Controller;

use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaFeature\FeatureToggles;
use DvsaMotTest\Controller\StartTestConfirmationController;
use DvsaMotTest\Service\AuthorisedClassesService;
use DvsaMotTest\Service\StartTestChangeService;
use DvsaMotTest\Specification\OfficialWeightSourceForVehicle;
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
        /* @var ParamObfuscator $paramObfuscator */
        $paramObfuscator = $serviceLocator->get(ParamObfuscator::class);
        /* @var CountryOfRegistrationCatalog $countryOfRegistrationCatalog */
        $countryOfRegistrationCatalog = $serviceLocator->get(CountryOfRegistrationCatalog::class);
        /* @var VehicleService $vehicleService */
        $vehicleService = $serviceLocator->get(VehicleService::class);
        /* @var StartTestChangeService $startTestChangeService */
        $startTestChangeService = $serviceLocator->get(StartTestChangeService::class);
        /* @var AuthorisedClassesService $authorisedClassesService */
        $authorisedClassesService = $serviceLocator->get(AuthorisedClassesService::class);
        /* @var MotFrontendIdentityProviderInterface $identityProvider */
        $identityProvider = $serviceLocator->get(MotFrontendIdentityProviderInterface::class);
        /** @var FeatureToggles $featureToggles*/
        $featureToggles = $serviceLocator->get(FeatureToggles::class);
        /** @var OfficialWeightSourceForVehicle $officialVehicleWeightSourceSpec */
        $officialVehicleWeightSourceSpec = new OfficialWeightSourceForVehicle();

        return new StartTestConfirmationController(
            $paramObfuscator,
            $countryOfRegistrationCatalog,
            $vehicleService,
            $startTestChangeService,
            $authorisedClassesService,
            $identityProvider,
            $officialVehicleWeightSourceSpec,
            $featureToggles
        );
    }
}
