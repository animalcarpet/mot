<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModuleTest\Factory\Controller;

use Application\Data\ApiPersonalDetails;
use Application\Service\CatalogService;
use Dashboard\Authorisation\ViewTradeRolesAssertion;
use Dashboard\Data\ApiDashboardResource;
use Dvsa\Mot\Frontend\PersonModule\Controller\PersonProfileController;
use Dvsa\Mot\Frontend\PersonModule\Factory\Controller\PersonProfileControllerFactory;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaClient\MapperFactory;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use UserAdmin\Service\UserAdminSessionManager;
use Application\Service\CanTestWithoutOtpService;

class PersonProfileControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            PersonProfileControllerFactory::class,
            PersonProfileController::class,
            [
                ApiPersonalDetails::class => ApiPersonalDetails::class,
                ApiDashboardResource::class => ApiDashboardResource::class,
                'CatalogService' => CatalogService::class,
                UserAdminSessionManager::class => UserAdminSessionManager::class,
                ViewTradeRolesAssertion::class => ViewTradeRolesAssertion::class,
                PersonProfileGuardBuilder::class => PersonProfileGuardBuilder::class,
                MapperFactory::class => MapperFactory::class,
                ContextProvider::class => ContextProvider::class,
                CanTestWithoutOtpService::class => CanTestWithoutOtpService::class,
                SecurityCardService::class => SecurityCardService::class,
                SecurityCardGuard::class => SecurityCardGuard::class,
                TwoFaFeatureToggle::class,
            ]
        );
    }
}
