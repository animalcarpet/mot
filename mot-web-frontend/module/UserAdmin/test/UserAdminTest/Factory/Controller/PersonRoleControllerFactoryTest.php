<?php

namespace UserAdminTest\Factory\Controller;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Controller\PersonRoleController;
use UserAdmin\Service\PersonRoleManagementService;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Controller\ControllerManager;
use UserAdmin\Factory\Controller\PersonRoleControllerFactory;

/**
 * Test for {@link \UserAdmin\Factory\Controller\PersonRoleControllerFactory}.
 */
class PersonRoleControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $serviceManager = new ServiceManager();

        $authorisationService = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $personRoleManagementService = XMock::of(PersonRoleManagementService::class);

        $serviceManager->setService('AuthorisationService', $authorisationService);
        $serviceManager->setService(PersonRoleManagementService::class, $personRoleManagementService);

        $plugins = $this->getMockBuilder(ControllerManager::class)->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $factory = new PersonRoleControllerFactory();

        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(PersonRoleController::class, $factoryResult);
    }
}
