<?php

namespace OrganisationApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Utility\Hydrator;
use DvsaCommonApi\Service\AddressService;
use DvsaCommonTest\TestUtils\XMock;
use OrganisationApi\Factory\Service\AddressServiceFactory;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class AddressServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    private $addressServiceFactory;
    private $entityManagerMock;
    private $serviceLocator;
    private $hydratorMock;

    public function setUp()
    {
        $this->addressServiceFactory = new AddressServiceFactory();
        $this->entityManagerMock = XMock::of(EntityManager::class, ['getRepository']);
        $this->hydratorMock = XMock::of(Hydrator::class);
        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService(EntityManager::class, $this->entityManagerMock);
        $this->serviceLocator->setService(Hydrator::class, $this->hydratorMock);
    }

    public function testFactoryReturnsService()
    {
        $service = $this->addressServiceFactory->createService($this->serviceLocator);
        $this->assertInstanceOf(AddressService::class, $service);
    }
}
