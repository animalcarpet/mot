<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardValidation\Service;

use Core\Service\LazyMotFrontendAuthorisationService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Service\RegisterCardServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;

class RegisterCardServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            RegisterCardServiceFactory::class,
            RegisterCardService::class,
            [
                AuthorisationService::class,
                'MotIdentityProvider' => MotFrontendIdentityProvider::class,
                MotAuthorisationServiceInterface::class => LazyMotFrontendAuthorisationService::class,
            ]
        );
    }
}
