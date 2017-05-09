<?php

namespace SiteApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Controller\MotTestInProgressController;
use SiteApi\Factory\Controller\MotTestInProgressControllerFactory;
use SiteApi\Service\MotTestInProgressService;
use Zend\ServiceManager\ServiceManager;

class MotTestInProgressControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(MotTestInProgressService::class, XMock::of(MotTestInProgressService::class));

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new MotTestInProgressControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(MotTestInProgressController::class, $factoryResult);
    }
}
