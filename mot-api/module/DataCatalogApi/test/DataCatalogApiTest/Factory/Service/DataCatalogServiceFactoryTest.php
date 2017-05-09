<?php

namespace DvsaCatalogApiTest\Factory\Service;

use DataCatalogApi\Factory\Service\DataCatalogServiceFactory;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;
use DataCatalogApi\Service\DataCatalogService;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;

/**
 * Class DataCatalogServiceFactoryTest.
 */
class DataCatalogServiceFactoryTest extends AbstractServiceTestCase
{
    private $serviceLocator;

    public function setUp()
    {
        $entityManagerMock = XMock::of(EntityManager::class);
        $doctrineObject = XMock::of(DoctrineObject::class);
        $authorisationService = XMock::of(AuthorisationServiceInterface::class);

        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService(EntityManager::class, $entityManagerMock);
        $this->serviceLocator->setService('Hydrator', $doctrineObject);
        $this->serviceLocator->setService('DvsaAuthorisationService', $authorisationService);
    }

    public function testDataCatalogServiceFactory()
    {
        $service = (new DataCatalogServiceFactory())->createService($this->serviceLocator);

        $this->assertInstanceOf(
            DataCatalogService::class,
            $service
        );
    }
}
