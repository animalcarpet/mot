<?php

namespace SiteApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Controller\SiteController;
use SiteApi\Factory\Controller\SiteControllerFactory;
use SiteApi\Service\SiteService;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SiteControllerFactoryTest.
 */
class SiteControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $tokenService = XMock::of(SiteService::class);
        $serviceManager->setService(SiteService::class, $tokenService);

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new SiteControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(SiteController::class, $factoryResult);
    }
}
