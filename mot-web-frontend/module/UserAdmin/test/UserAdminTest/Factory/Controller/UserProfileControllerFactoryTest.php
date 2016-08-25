<?php

namespace UserAdminTest\Factory\Controller;

use Application\Service\CatalogService;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dashboard\Authorisation\ViewTradeRolesAssertion;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\RegisteredCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Controller\UserProfileController;
use UserAdmin\Service\HelpdeskAccountAdminService;
use UserAdmin\Service\PersonRoleManagementService;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Controller\ControllerManager;
use UserAdmin\Factory\Controller\UserProfileControllerFactory;

/**
 * Test for {@link \UserAdmin\Factory\Controller\UserProfileControllerFactory}.
 */
class UserProfileControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $serviceManager = new ServiceManager();

        $userAccountAdminService = XMock::of(HelpdeskAccountAdminService::class);
        $serviceManager->setService(HelpdeskAccountAdminService::class, $userAccountAdminService);

        $authorisationService = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $serviceManager->setService("AuthorisationService", $authorisationService);

        $testerQualificationStatus = XMock::of(TesterGroupAuthorisationMapper::class);
        $serviceManager->setService(TesterGroupAuthorisationMapper::class, $testerQualificationStatus);

        $personRoleManagementService = XMock::of(PersonRoleManagementService::class);
        $serviceManager->setService(PersonRoleManagementService::class, $personRoleManagementService);

        $catalogService = XMock::of(CatalogService::class);
        $serviceManager->setService("CatalogService", $catalogService);

        $viewTradeRolesAssertion = XMock::of(ViewTradeRolesAssertion::class);
        $serviceManager->setService(ViewTradeRolesAssertion::class, $viewTradeRolesAssertion);

        $registeredCardService = XMock::of(RegisteredCardService::class);
        $serviceManager->setService(RegisteredCardService::class, $registeredCardService);

        $twoFaFeatureToggle = XMock::of(TwoFaFeatureToggle::class);
        $serviceManager->setService(TwoFaFeatureToggle::class, $twoFaFeatureToggle);

        $serviceManager->setService(
            MotConfig::class,
            XMock::of(MotConfig::class)
        );

        $plugins = $this->getMock(ControllerManager::class);
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $factory = new UserProfileControllerFactory();

        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(UserProfileController::class, $factoryResult);
    }
}
