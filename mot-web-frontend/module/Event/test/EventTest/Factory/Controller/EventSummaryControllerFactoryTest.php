<?php

namespace EventTest\Factory\Controller;

use Core\Service\SessionService;
use DvsaCommonTest\TestUtils\XMock;
use Event\Controller\EventSummaryController;
use Event\Factory\Controllers\EventSummaryControllerFactory;
use Event\Service\EventStepService;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Controller\ControllerManager;

class EventSummaryControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @throws \Exception
     */
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $session = XMock::of(SessionService::class);
        $serviceManager->setService(SessionService::class, $session);

        $service = XMock::of(EventStepService::class);
        $serviceManager->setService(EventStepService::class, $service);

        $plugins = $this->getMockBuilder(ControllerManager::class)->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        //Create Factory
        $factory = new EventSummaryControllerFactory();
        $factoryResult = $factory->createService($plugins);
        $this->assertInstanceOf(EventSummaryController::class, $factoryResult);
    }
}
