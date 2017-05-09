<?php

namespace DvsaAuthorisationTest\Factory;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Factory\UserRoleServiceFactory;
use DvsaAuthorisation\Service\UserRoleService;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use Zend\ServiceManager\ServiceManager;

/**
 * Class UserRoleServiceFactoryTest.
 */
class UserRoleServiceFactoryTest extends AbstractServiceTestCase
{
    /* @var UserRoleServiceFactory $userRoleServiceFactory */
    private $userRoleServiceFactory;

    private $serviceLocator;
    private $entityManagerMock;
    private $entityRepositoryMock;

    public function setUp()
    {
        $this->userRoleServiceFactory = new UserRoleServiceFactory();
        $this->entityManagerMock = XMock::of(EntityManager::class, ['getRepository']);
        $this->entityRepositoryMock = XMock::of(EntityRepository::class);
        $this->serviceLocator = new ServiceManager();
        $this->serviceLocator->setService(EntityManager::class, $this->entityManagerMock);
    }

    public function testAuthorisationServiceFactoryReturnsInstance()
    {
        $this->entityManagerMock->expects($this->at(0))
            ->method('getRepository')
            ->willReturn($this->entityRepositoryMock);
        $this->entityManagerMock->expects($this->at(1))
            ->method('getRepository')
            ->willReturn($this->entityRepositoryMock);
        $this->entityManagerMock->expects($this->at(2))
            ->method('getRepository')
            ->willReturn($this->entityRepositoryMock);
        $this->assertInstanceOf(
            UserRoleService::class,
            $this->userRoleServiceFactory->createService($this->serviceLocator)
        );
    }
}
