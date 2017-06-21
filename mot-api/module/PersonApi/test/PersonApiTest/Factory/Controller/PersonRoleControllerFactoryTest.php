<?php

namespace PersonApi\test\PersonApiTest\Factory\Controller;

use Zend\ServiceManager\ServiceManager;
use DvsaCommonTest\TestUtils\XMock;
use PersonApi\Controller\PersonRoleController;
use PersonApi\Factory\Controller\PersonRoleControllerFactory;
use PersonApi\Service\PersonRoleService;

class PersonRoleControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $service = XMock::of(PersonRoleService::class);
        $serviceManager->setService(PersonRoleService::class, $service);

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new PersonRoleControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(PersonRoleController::class, $factoryResult);
    }
}
