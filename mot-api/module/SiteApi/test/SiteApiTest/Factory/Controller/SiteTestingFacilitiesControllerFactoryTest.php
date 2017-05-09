<?php

namespace SiteApiTest\Factory\Controller;

use DvsaCommonTest\TestUtils\XMock;
use SiteApi\Controller\SiteTestingFacilitiesController;
use SiteApi\Factory\Controller\SiteTestingFacilitiesControllerFactory;
use SiteApi\Service\SiteTestingFacilitiesService;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SiteTestingFacilitiesControllerFactoryTest.
 */
class SiteTestingFacilitiesControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        //Given
        $serviceManager = new ServiceManager();

        $repository = XMock::of(SiteTestingFacilitiesService::class);
        $serviceManager->setService(SiteTestingFacilitiesService::class, $repository);

        $plugins = $this->getMock('Zend\Mvc\Controller\ControllerManager');
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        // Create the factory
        $factory = new SiteTestingFacilitiesControllerFactory();

        //When
        $factoryResult = $factory->createService($plugins);

        //Then
        $this->assertInstanceOf(SiteTestingFacilitiesController::class, $factoryResult);
    }
}
