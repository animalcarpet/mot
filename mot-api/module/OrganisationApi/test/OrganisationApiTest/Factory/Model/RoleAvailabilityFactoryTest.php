<?php

namespace OrganisationApiTest\Factory\Model;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommonTest\TestUtils\XMock;
use OrganisationApi\Factory\Model\RoleAvailabilityFactory;
use OrganisationApi\Model\RoleAvailability;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class RoleAvailabilityFactoryTest extends PHPUnit_Framework_TestCase
{
    private $roleAvailabilityFactory;
    private $entityManagerMock;
    private $entityRepositoryMock;
    private $serviceLocator;
    private $authorisationServiceMock;

    public function setUp()
    {
        $this->roleAvailabilityFactory = new RoleAvailabilityFactory();
        $this->authorisationServiceMock = XMock::of(AuthorisationService::class);
        $this->entityManagerMock = XMock::of(EntityManager::class, ['getRepository']);
        $this->entityRepositoryMock = XMock::of(EntityRepository::class);
        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService(EntityManager::class, $this->entityManagerMock);
        $this->serviceLocator->setService('DvsaAuthorisationService', $this->authorisationServiceMock);
    }

    public function testFactoryReturnsService()
    {
        $this->entityManagerMock->expects($this->at(0))
            ->method('getRepository')
            ->willReturn($this->entityRepositoryMock);
        $service = $this->roleAvailabilityFactory->createService($this->serviceLocator);
        $this->assertInstanceOf(RoleAvailability::class, $service);
    }
}
