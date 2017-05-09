<?php

namespace DvsaEventApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\EventType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use DvsaEventApi\Service\EventPersonCreationService;
use DvsaEventApi\Service\RecordEventResult;

class EventPersonCreationServiceTest extends \PHPUnit_Framework_TestCase
{
    const USER_ID = 105;
    const EVENT_CODE = 'SCO';
    const DESCRIPTION = 'test description';

    private $personRepository;

    private $eventTypeRepository;

    private $entityManager;

    public function setUp()
    {
        $this->personRepository = XMock::of(PersonRepository::class);
        $this->eventTypeRepository = XMock::of(EntityRepository::class);
        $this->entityManager = XMock::of(EntityManager::class);
    }

    public function testWhenPersonNotFoundExceptionThrown()
    {
        $this->setExpectedException(\Exception::class, 'Unable to find person with id: '.self::USER_ID);

        $this->personRepository
            ->expects($this->once())
            ->method('find')
            ->with(self::USER_ID)
            ->willReturn(null);

        $this->getService()->createPersonEvent(self::USER_ID, self::EVENT_CODE, self::DESCRIPTION);
    }

    public function testWhenEventTypeNotFoundExceptionThrown()
    {
        $this->withPersonFound();
        $this->setExpectedException(\Exception::class, 'Unable to find event type with code: '.self::EVENT_CODE);

        $this->eventTypeRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => self::EVENT_CODE])
            ->willReturn(null);

        $this->getService()->createPersonEvent(self::USER_ID, self::EVENT_CODE, self::DESCRIPTION);
    }

    public function testWhenEventAddedSuccessfullyEventResultObjectReturned()
    {
        $this->withPersonFound();
        $this->withEventTypeFound();

        $this->entityManager
            ->expects($this->once())
            ->method('beginTransaction');

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->entityManager
            ->expects($this->once())
            ->method('commit');

        $actual = $this->getService()->createPersonEvent(self::USER_ID, self::EVENT_CODE, self::DESCRIPTION);
        $this->assertInstanceOf(RecordEventResult::class, $actual);
    }

    private function getService()
    {
        return new EventPersonCreationService(
            $this->personRepository,
            $this->eventTypeRepository,
            $this->entityManager
        );
    }

    private function withPersonFound()
    {
        $this->personRepository
            ->expects($this->once())
            ->method('find')
            ->with(self::USER_ID)
            ->willReturn(new Person());
    }

    private function withEventTypeFound()
    {
        $this->eventTypeRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['code' => self::EVENT_CODE])
            ->willReturn(new EventType());
    }
}
