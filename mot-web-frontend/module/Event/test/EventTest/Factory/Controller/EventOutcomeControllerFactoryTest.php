<?php

namespace EventTest\Factory\Controller;

use Core\Service\SessionService;
use DvsaCommonTest\TestUtils\XMock;
use Event\Controller\EventOutcomeController;
use Event\Factory\Controllers\EventOutcomeControllerFactory;
use Event\Service\EventStepService;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Controller\ControllerManager;

class EventOutcomeControllerFactoryTest extends \PHPUnit_Framework_TestCase
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

        $plugins = $this->getMock(ControllerManager::class);
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        //Create Factory
        $factory = new EventOutcomeControllerFactory();
        $factoryResult = $factory->createService($plugins);
        $this->assertInstanceOf(EventOutcomeController::class, $factoryResult);
    }
}
