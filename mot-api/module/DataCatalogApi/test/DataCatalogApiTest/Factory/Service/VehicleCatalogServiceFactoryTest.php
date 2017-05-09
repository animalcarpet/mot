<?php

namespace DvsaCatalogApiTest\Factory\Service;

use DataCatalogApi\Factory\Service\VehicleCatalogServiceFactory;
use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;
use DataCatalogApi\Service\VehicleCatalogService;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;

/**
 * Class VehicleCatalogServiceFactoryTest.
 */
class VehicleCatalogServiceFactoryTest extends AbstractServiceTestCase
{
    private $serviceLocator;
    private $entityManagerMock;

    public function setUp()
    {
        $this->entityManagerMock = XMock::of(EntityManager::class);

        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService(EntityManager::class, $this->entityManagerMock);
    }

    public function testVehicleCatalogServiceFactory()
    {
        $service = (new VehicleCatalogServiceFactory())->createService($this->serviceLocator);

        $this->assertInstanceOf(
            VehicleCatalogService::class,
            $service
        );
    }
}
