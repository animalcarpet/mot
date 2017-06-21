<?php

namespace PersonApiTest\Factory\Controller;

use DvsaCommon\Validator\UsernameValidator;
use DvsaCommonTest\Bootstrap;
use PersonApi\Controller\PersonByLoginController;
use PersonApi\Factory\Controller\PersonByLoginControllerFactory;

/**
 * Class PersonByLoginControllerFactoryTest.
 */
class PersonByLoginControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsPersonByLoginControllerInstance()
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
        $factory = new PersonByLoginControllerFactory();

        $this->assertInstanceOf(PersonByLoginController::class, $factory->createService($controllerManager));
    }
}
