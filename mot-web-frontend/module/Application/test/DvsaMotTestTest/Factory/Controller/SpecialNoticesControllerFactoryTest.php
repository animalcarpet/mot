<?php

namespace DvsaMotTestTest\Factory\Controller;

use DvsaCommonTest\Bootstrap;
use DvsaMotTest\Factory\Controller\SpecialNoticesControllerFactory;

/**
 * Class SpecialNoticesControllerFactoryTest.
 *
 * @covers \DvsaMotTest\Controller\SpecialNoticesController
 */
class SpecialNoticesControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsSpecialNoticesControllerInstance()
    {
        $serviceManager = Bootstrap::getServiceManager();

        $controllerManager = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $controllerManager->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));
        $factory = new SpecialNoticesControllerFactory();

        $this->assertInstanceOf('DvsaMotTest\Controller\SpecialNoticesController', $factory->createService($controllerManager));
    }
}
