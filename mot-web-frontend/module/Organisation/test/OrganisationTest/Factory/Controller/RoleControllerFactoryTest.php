<?php

namespace OrganisationTest\Factory\Controller;

use DvsaCommon\Validator\UsernameValidator;
use DvsaCommonTest\TestUtils\XMock;
use Organisation\Controller\RoleController;
use Organisation\Factory\Controller\RoleControllerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class RoleControllerFactoryTest.
 */
class RoleControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsRoleControllerInstance()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(UsernameValidator::class, XMock::of(UsernameValidator::class));
        $serviceManager->setService('HTMLPurifier', XMock::of(\HTMLPurifier::class));

        /** @var ServiceLocatorInterface|MockObject $plugins */
        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($serviceManager);

        $factory = new RoleControllerFactory();

        $this->assertInstanceOf(RoleController::class, $factory->createService($plugins));
    }
}
