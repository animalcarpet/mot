<?php

namespace SiteApiTest\Factory\Controller;

use SiteApi\Controller\SiteEventController;
use SiteApi\Factory\Controller\SiteEventControllerFactory;
use SiteApi\Service\SiteEventService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;
use DvsaCommonTest\TestUtils\XMock;

class SiteEventControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(SiteEventService::class, XMock::of(SiteEventService::class));

        $plugins = $this->getMockBuilder(ControllerManager::class)->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new SiteEventControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(SiteEventController::class, $factoryResult);
    }
}
