<?php

namespace EventTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use Event\Factory\Controllers\EventRecordControllerFactory;
use Event\Controller\EventRecordController;
use Event\Service\EventStepService;
use Event\Service\EventSessionService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

class EventRecordControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testFactory()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService(EventStepService::class, XMock::of(EventStepService::class));
        $serviceManager->setService(EventSessionService::class, XMock::of(EventSessionService::class));

        $plugins = $this->getMockBuilder(ControllerManager::class)->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $factory = new EventRecordControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(EventRecordController::class, $factoryResult);
    }
}
