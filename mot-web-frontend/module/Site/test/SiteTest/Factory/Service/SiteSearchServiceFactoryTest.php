<?php

namespace SiteTest\Factory\Service;

use Site\Service\SiteSearchService;
use Site\Factory\Service\SiteSearchServiceFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SiteSearchServiceFactoryTest.
 */
class SiteSearchServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new SiteSearchServiceFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(SiteSearchService::class, $factoryResult);
    }
}
