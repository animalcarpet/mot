<?php

namespace DvsaMotTestTest\Factory\Controller;

use Core\Catalog\CountryOfRegistration\CountryOfRegistrationCatalog;
use Core\Service\MotFrontendIdentityProviderInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use DvsaFeature\FeatureToggles;
use DvsaMotTest\Controller\StartTestConfirmationController;
use DvsaMotTest\Factory\Controller\StartTestConfirmationControllerFactory;
use DvsaMotTest\Service\AuthorisedClassesService;
use DvsaMotTest\Service\StartTestChangeService;
use DvsaMotTest\Specification\OfficialWeightSourceForVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;


class StartTestConfirmationControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            StartTestConfirmationControllerFactory::class,
            StartTestConfirmationController::class,
            [
                ParamObfuscator::class,
                CountryOfRegistrationCatalog::class,
                VehicleService::class,
                StartTestChangeService::class,
                AuthorisedClassesService::class,
                MotFrontendIdentityProviderInterface::class,
                OfficialWeightSourceForVehicle::class,
                FeatureToggles::class
            ]
        );
    }

}