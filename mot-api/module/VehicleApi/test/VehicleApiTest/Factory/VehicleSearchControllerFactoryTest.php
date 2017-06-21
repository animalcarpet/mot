<?php

namespace VehicleApiTest\Controller;

use DvsaCommonTest\TestUtils\XMock;
use VehicleApi\Controller\VehicleSearchController;
use VehicleApi\Factory\Controller\VehicleSearchControllerFactory;
use Zend\ServiceManager\ServiceManager;
use VehicleApi\Service\VehicleSearchService;
use DvsaEntities\DqlBuilder\SearchParam\VehicleSearchParam;

class VehicleSearchControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testVehicleSearchControllerFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(VehicleSearchService::class, XMock::of(VehicleSearchService::class));
        $serviceManager->setService(VehicleSearchParam::class, XMock::of(VehicleSearchParam::class));

        $plugins = $this->getMockBuilder('Zend\Mvc\Controller\ControllerManager')->disableOriginalConstructor()->getMock();
        $plugins->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($serviceManager);

        // Create the factory
        $factory = new VehicleSearchControllerFactory();
        $factoryResult = $factory->createService($plugins);

        $this->assertInstanceOf(VehicleSearchController::class, $factoryResult);
    }
}
