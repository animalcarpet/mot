<?php

namespace DvsaEventApiTest\Factory\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\EventRepository;
use DvsaEntities\Repository\EventTypeOutcomeCategoryMapRepository;
use DvsaEventApi\Factory\Service\EventServiceFactory;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaEventApi\Service\EventService;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;

/**
 * Class EventServiceFactoryTest.
 */
class EventServiceFactoryTest extends AbstractServiceTestCase
{
    /* @var EventServiceFactory $eventServiceFactory */
    private $eventServiceFactory;

    private $serviceLocatorMock;
    private $authServiceMock;
    private $entityManagerMock;
    private $hydratorMock;
    private $eventRepositoryMock;
    private $eventTypeRepositoryMock;

    public function setUp()
    {
        $this->eventServiceFactory = new EventServiceFactory();
        $this->serviceLocatorMock = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->authServiceMock = XMock::of(AuthorisationServiceInterface::class);
        $this->entityManagerMock = XMock::of(EntityManager::class);
        $this->hydratorMock = XMock::of(DoctrineObject::class);
        $this->eventRepositoryMock = XMock::of(EventRepository::class);
        $this->eventTypeRepositoryMock = XMock::of(EntityRepository::class);
    }

    public function testEventServiceGetList()
    {
        $this->serviceLocatorMock->expects($this->at(0))
            ->method('get')
            ->willReturn($this->entityManagerMock);
        $this->serviceLocatorMock->expects($this->at(1))
            ->method('get')
            ->willReturn($this->authServiceMock);
        $this->serviceLocatorMock->expects($this->at(2))
            ->method('get')
            ->willReturn($this->hydratorMock);

        $this->entityManagerMock->expects($this->at(0))
            ->method('getRepository')
            ->willReturn($this->eventRepositoryMock);
        $this->entityManagerMock->expects($this->at(1))
            ->method('getRepository')
            ->willReturn($this->eventTypeRepositoryMock);
        $this->entityManagerMock->expects($this->at(2))
            ->method('getRepository')
            ->willReturn(XMock::of(EntityRepository::class));
        $this->entityManagerMock->expects($this->at(3))
            ->method('getRepository')
            ->willReturn(XMock::of(EntityRepository::class));
        $this->entityManagerMock->expects($this->at(4))
            ->method('getRepository')
            ->willReturn(XMock::of(EventTypeOutcomeCategoryMapRepository::class));

        $this->assertInstanceOf(
            EventService::class,
            $this->eventServiceFactory->createService($this->serviceLocatorMock)
        );
    }
}
