<?php

namespace OrganisationTest\Factory\Controller;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use Organisation\Controller\AuthorisedExaminerController;
use Organisation\Factory\Controller\AuthorisedExaminerControllerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class AuthorisedExaminerControllerFactoryTest
 * @package OrganisationTest\Factory\Controller
 */
class AuthorisedExaminerControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService("AuthorisationService", XMock::of(MotFrontendAuthorisationServiceInterface::class));
        $serviceManager->setService(MapperFactory::class, XMock::of(MapperFactory::class));
        $serviceManager->setService('MotIdentityProvider', XMock::of(MotIdentityProviderInterface::class));

        /** @var ServiceLocatorInterface|MockObject $plugins */
        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($serviceManager);

        //  --  check   --
        $this->assertInstanceOf(
            AuthorisedExaminerController::class,
            (new AuthorisedExaminerControllerFactory())->createService($plugins)
        );
    }
}
