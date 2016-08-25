<?php

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Listener\Factory;

use Dvsa\Mot\Frontend\AuthenticationModule\Controller\SecurityController;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Controller\SecurityControllerFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\AuthenticationAccountLockoutViewModelBuilder;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\GotoUrlService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\IdentitySessionStateService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\LoginCsrfCookieService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLoginService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use Zend\Authentication\AuthenticationService;
use Zend\Http\Request;
use Zend\Http\Response;

class SecurityControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            SecurityControllerFactory::class,
            SecurityController::class,
            [
                'Request' => Request::class,
                'Response' => Response::class,
                GotoUrlService::class,
                IdentitySessionStateService::class,
                WebLoginService::class,
                LoginCsrfCookieService::class => LoginCsrfCookieService::class,
                'ZendAuthenticationService' => AuthenticationService::class,
                AuthenticationAccountLockoutViewModelBuilder::class,
                TwoFaFeatureToggle::class
            ]
        );
    }
}
