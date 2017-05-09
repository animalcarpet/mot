<?php

namespace PersonApiTest\Factory\Controller;

use PersonApi\Controller\PersonEventController;
use PersonApi\Factory\Controller\PersonEventControllerFactory;
use PersonApi\Service\PersonEventService;
use Zend\ServiceManager\ServiceManager;
use DvsaCommonTest\TestUtils\XMock;

class PersonEventControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $serviceManager = new ServiceManager();

        $service = XMock::of(PersonEventService::class);
        $serviceManager->setService(PersonEventService::class, $service);

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new PersonEventControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(PersonEventController::class, $factoryResult);
    }
}
