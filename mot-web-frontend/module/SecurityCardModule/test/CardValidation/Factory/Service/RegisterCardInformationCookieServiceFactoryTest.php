<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\CardValidation\Factory\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Factory\Service\RegisterCardInformationCookieServiceFactory;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardInformationCookieService;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use Core\Service\MotFrontendIdentityProvider;

class RegisterCardInformationCookieServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            RegisterCardInformationCookieServiceFactory::class,
            RegisterCardInformationCookieService::class,
            [
                'MotIdentityProvider' => MotFrontendIdentityProvider::class,
            ]
        );
    }
}