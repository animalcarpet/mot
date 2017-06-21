<?php

namespace SiteApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Controller\SiteSearchController;
use SiteApi\Factory\Controller\SiteSearchControllerFactory;
use SiteApi\Service\SiteSearchService;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SiteSearchControllerFactoryTest.
 */
class SiteSearchControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $service = XMock::of(SiteSearchService::class);
        $serviceManager->setService(SiteSearchService::class, $service);

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new SiteSearchControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(SiteSearchController::class, $factoryResult);
    }
}
