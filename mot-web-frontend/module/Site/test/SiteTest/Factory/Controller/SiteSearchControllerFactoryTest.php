<?php

namespace SiteTest\Factory\Controller;

use DvsaClient\MapperFactory;
use DvsaCommonTest\TestUtils\XMock;
use Site\Controller\SiteSearchController;
use Site\Factory\Controller\SiteSearchControllerFactory;
use Site\Service\SiteSearchService;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SiteSearchControllerFactoryTest.
 */
class SiteSearchControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $service = XMock::of(MapperFactory::class);
        $serviceManager->setService(MapperFactory::class, $service);
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
