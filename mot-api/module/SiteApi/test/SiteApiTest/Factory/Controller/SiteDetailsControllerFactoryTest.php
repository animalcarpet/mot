<?php

namespace SiteApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Controller\SiteDetailsController;
use SiteApi\Factory\Controller\SiteDetailsControllerFactory;
use SiteApi\Service\SiteDetailsService;
use Zend\ServiceManager\ServiceManager;

class SiteDetailsControllerFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceMock = XMock::of(SiteDetailsService::class);
        $serviceManager->setService(SiteDetailsService::class, $serviceMock);

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $factory = new SiteDetailsControllerFactory();

        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(SiteDetailsController::class, $factoryResult);
    }

}
