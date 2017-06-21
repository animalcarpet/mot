<?php

namespace DvsaMotApiTest\Factory\Controller;

use DvsaCommon\Validator\UsernameValidator;
use DvsaCommonTest\Bootstrap;
use DvsaMotApi\Controller\UserController;
use DvsaMotApi\Factory\Controller\UserControllerFactory;

/**
 * Class UserControllerFactoryTest.
 */
class UserControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsUserControllerInstance()
    {
        $serviceManager = Bootstrap::getServiceManager();

        $usernameValidatorMock = $this
            ->getMockBuilder(UsernameValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceManager->setService(UsernameValidator::class, $usernameValidatorMock);

        $controllerManager = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $controllerManager->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));
        $factory = new UserControllerFactory();

        $this->assertInstanceOf(UserController::class, $factory->createService($controllerManager));
    }
}
