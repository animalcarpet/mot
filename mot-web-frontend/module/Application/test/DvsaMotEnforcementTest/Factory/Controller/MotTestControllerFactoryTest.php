<?php

namespace DvsaMotEnforcementTest\Factory\Controller;

use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotEnforcement\Controller\MotTestController;
use DvsaMotEnforcement\Factory\Controller\MotTestControllerFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class MotTestControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testEventServiceGetList()
    {
        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), XMock::of(ParamObfuscator::class));

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($mockServiceLocator));

        $this->assertInstanceOf(
            MotTestController::class,
            (new MotTestControllerFactory())->createService($plugins)
        );
    }
}
