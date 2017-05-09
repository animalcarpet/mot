<?php

namespace DashboardTest\Factory\Controller;

use Account\Service\SecurityQuestionService;
use Application\Data\ApiPersonalDetails;
use Application\Service\CatalogService;
use Application\Service\LoggedInUserManager;
use Dashboard\Authorisation\ViewTradeRolesAssertion;
use Dashboard\Controller\UserHomeController;
use Dashboard\Data\ApiDashboardResource;
use Dashboard\Factory\Controller\UserHomeControllerFactory;
use Dashboard\PersonStore;
use Dashboard\Security\DashboardGuard;
use Dashboard\Service\TradeRolesAssociationsService;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\ServiceManager\ServiceManager;
use Core\Authorisation\Assertion\WebAcknowledgeSpecialNoticeAssertion;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;

/**
 * Class PasswordResetControllerFactoryTest.
 */
class UserHomeControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(ApiPersonalDetails::class, XMock::of(ApiPersonalDetails::class));
        $serviceManager->setService(ApiDashboardResource::class, XMock::of(ApiDashboardResource::class));
        $serviceManager->setService(PersonStore::class, XMock::of(PersonStore::class));
        $serviceManager->setService('LoggedInUserManager', XMock::of(LoggedInUserManager::class));
        $serviceManager->setService('CatalogService', XMock::of(CatalogService::class));
        $serviceManager->setService(
            WebAcknowledgeSpecialNoticeAssertion::class,
            XMock::of(WebAcknowledgeSpecialNoticeAssertion::class)
        );
        $serviceManager->setService(SecurityQuestionService::class, XMock::of(SecurityQuestionService::class));
        $serviceManager->setService(UserAdminSessionManager::class, XMock::of(UserAdminSessionManager::class));
        $serviceManager->setService(TesterGroupAuthorisationMapper::class, XMock::of(TesterGroupAuthorisationMapper::class));
        $serviceManager->setService('AuthorisationService', XMock::of(MotAuthorisationServiceInterface::class));
        $serviceManager->setService(ViewTradeRolesAssertion::class, XMock::of(ViewTradeRolesAssertion::class));
        $serviceManager->setService(TradeRolesAssociationsService::class, XMock::of(TradeRolesAssociationsService::class));
        $serviceManager->setService(DashboardGuard::class, XMock::of(DashboardGuard::class));

        $plugins = XMock::of(\Zend\Mvc\Controller\ControllerManager::class, ['getServiceLocator']);
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($serviceManager);

        // Create the factory
        $factory = new UserHomeControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(UserHomeController::class, $factoryResult);
    }
}
