<?php

namespace VehicleTest\Factory\Service;

use Vehicle\Factory\Service\VehicleCatalogServiceFactory;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\Service\VehicleCatalogService;

class VehicleCatalogServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);

        $mockServiceLocator->expects($this->at(0))->method('get')
                           ->willReturn(XMock::of(HttpRestJsonClient::class));

        $factory = new VehicleCatalogServiceFactory();

        $this->assertInstanceOf(
            VehicleCatalogService::class,
            $factory->createService($mockServiceLocator)
        );
    }
}
