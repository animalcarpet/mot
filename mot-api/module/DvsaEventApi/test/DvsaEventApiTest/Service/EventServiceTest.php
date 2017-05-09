<?php

namespace DvsaEventApiTest\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Dto\Event\EventDto;
use DvsaCommon\Dto\Event\EventFormDto;
use DvsaCommon\Dto\Event\EventListDto;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Event;
use DvsaEntities\Entity\EventType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\EventRepository;
use DvsaEventApi\Service\EventService;
use DvsaEventApi\Service\Mapper\EventListMapper;
use Zend\Log\Logger;
use DvsaEntities\Repository\EventTypeOutcomeCategoryMapRepository;

/**
 * Class EventServiceTest.
 */
class EventServiceTest extends AbstractServiceTestCase
{
    const ID_TEST = 2;
    const TYPE_AE = 'ae';
    const TYPE_SITE = 'site';
    const TYPE_PERSON = 'person';
    const TYPE_INVALID = 'invalid';
    const EVENT_ID = 1;
    const EVENT_DESCRIPTION = 'Description';

    /* @var EventService $eventServiceFactory */
    private $eventService;

    private $authServiceMock;
    private $entityManagerMock;
    private $eventRepositoryMock;
    private $eventTypeRepositoryMock;
    private $eventCategoryRepositoryMock;
    private $eventOutcomeRepositoryMock;
    private $eventTypeOutcomeCategoryMapRepositoryMock;
    private $hydratorMock;
    private $mockLogger;

    public function setUp()
    {
        $this->authServiceMock = XMock::of(AuthorisationServiceInterface::class);
        $this->entityManagerMock = XMock::of(EntityManager::class);
        $this->eventRepositoryMock = XMock::of(EventRepository::class);
        $this->eventTypeRepositoryMock = XMock::of(EntityRepository::class);
        $this->eventCategoryRepositoryMock = XMock::of(EntityRepository::class);
        $this->eventOutcomeRepositoryMock = XMock::of(EntityRepository::class);
        $this->eventTypeOutcomeCategoryMapRepositoryMock = XMock::of(EventTypeOutcomeCategoryMapRepository::class);
        $this->hydratorMock = XMock::of(DoctrineObject::class);
        $this->mockLogger = XMock::of(Logger::class);

        $this->eventService = new EventService(
            $this->authServiceMock,
            $this->entityManagerMock,
            $this->eventRepositoryMock,
            $this->eventTypeRepositoryMock,
            $this->eventCategoryRepositoryMock,
            $this->eventOutcomeRepositoryMock,
            $this->eventTypeOutcomeCategoryMapRepositoryMock,
            $this->hydratorMock,
            new EventListMapper()
        );
        $this->eventService->setLogger($this->mockLogger);
    }

    public function testEventServiceGetListWithValidType()
    {
        $dto = new EventFormDto();

        $this->eventRepositoryMock->expects($this->any())
            ->method('findEvents')
            ->willReturn([]);

        $this->assertInstanceOf(EventListDto::class, $this->eventService->getList(self::ID_TEST, self::TYPE_AE, $dto));
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Invalid type passed, the type of event list must be ae/site/person
     */
    public function testEventServiceGetListWithInValidType()
    {
        $dto = new EventFormDto();

        $this->assertInstanceOf(
            EventListDto::class,
            $this->eventService->getList(self::ID_TEST, self::TYPE_INVALID, $dto)
        );
    }

    public function testEventServiceGetListForSite()
    {
        $dto = new EventFormDto();

        $this->eventRepositoryMock->expects($this->any())
            ->method('findEvents')
            ->willReturn([]);

        $this->assertInstanceOf(
            EventListDto::class,
            $this->eventService->getList(self::ID_TEST, self::TYPE_SITE, $dto)
        );
    }

    public function testEventServiceGetListForPerson()
    {
        $dto = new EventFormDto();

        $this->eventRepositoryMock->expects($this->any())
            ->method('findEvents')
            ->willReturn([]);

        $this->assertInstanceOf(
            EventListDto::class,
            $this->eventService->getList(self::ID_TEST, self::TYPE_PERSON, $dto)
        );
    }

    public function testEventServiceGet()
    {
        $date = new \DateTime();
        $type = new EventType();
        $type->setDescription(self::EVENT_DESCRIPTION);

        $event = new Event();
        $event->setId(self::EVENT_ID);
        $event->setShortDescription(self::EVENT_DESCRIPTION);
        $event->setEventDate($date);
        $event->setEventType($type);

        $this->eventRepositoryMock->expects($this->any())
            ->method('findEvent')
            ->willReturn($event);

        $this->assertInstanceOf(
            EventDto::class,
            $this->eventService->get(self::EVENT_ID)
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testFailsWhenNonIntegerEventTypeGiven1()
    {
        $this->eventService->addEvent([], '', new \DateTime());
    }

    /**
     * @expectedException \Exception
     */
    public function testFailsWhenNonIntegerEventTypeGiven2()
    {
        $this->eventService->addEvent('Hello', '', new \DateTime());
    }

    /**
     * @expectedException \Exception
     */
    public function testFailsWhenNonIntegerEventTypeGiven3()
    {
        $this->eventService->addEvent(null, '', new \DateTime());
    }

    /**
     * @expectedException \Exception
     */
    public function testFailsIfIllegalValueGiven()
    {
        $this->assertFalse($this->eventService->addEvent(-1, '', new \DateTime()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAddEventWithFailedEventTypeLookupThrowsException()
    {
        $this->eventTypeRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => EventTypeCode::MOT_MANAGEMENT_TRAINING])
            ->willThrowException(new \InvalidArgumentException('FAIL: event type lookup'));

        $this->mockLogger->expects($this->once())
            ->method('log')
            ->with(Logger::ERR);

        $this->eventService->addEvent(
            EventTypeCode::MOT_MANAGEMENT_TRAINING, 'this is a test', new \DateTime()
        );
    }

    /**
     * @expectedException \Exception
     */
    public function testAddEventFailsWhenNullReturnedFromRepositoryForEventTypeLookup()
    {
        $this->eventTypeRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => EventTypeCode::MOT_MANAGEMENT_TRAINING])
            ->willReturn(null);

        $this->mockLogger->expects($this->once())
            ->method('log')
            ->with(Logger::ERR);

        $this->eventService->addEvent(
            EventTypeCode::MOT_MANAGEMENT_TRAINING, 'this is a test', new \DateTime()
        );
    }

    public function testAddEventWorksWithAllGoodParameters()
    {
        $eventType = new EventType();

        $this->eventTypeRepositoryMock->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => EventTypeCode::MOT_MANAGEMENT_TRAINING])
            ->willReturn($eventType);

        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->willReturn(true);

        $this->entityManagerMock->expects($this->once())
            ->method('flush')
            ->willReturn(true);

        $this->assertInstanceOf(
            Event::class,
            $this->eventService->addEvent(
                EventTypeCode::MOT_MANAGEMENT_TRAINING, 'this is a test', new \DateTime()
            )
        );
    }

    public function testIsEventCreatedBy()
    {
        $person = new Person();

        $this->eventRepositoryMock->expects($this->at(0))
            ->method('isEventCreatedBy')
            ->with($person, EventTypeCode::USER_CLAIMS_ACCOUNT)
            ->willReturn(true);

        $this->assertEquals(
            true,
            $this->eventService->isEventCreatedBy(new Person(), EventTypeCode::USER_CLAIMS_ACCOUNT)
        );
    }
}
