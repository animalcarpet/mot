<?php

namespace DvsaEventApiTest\Service\Mapper;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Event\EventDto;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaEntities\Entity\Event;
use DvsaEntities\Entity\EventOutcome;
use DvsaEntities\Entity\EventType;
use DvsaEntities\Entity\Person;
use DvsaEventApi\Service\Mapper\EventListMapper;
use Zend\Stdlib\DateTime;

/**
 * Class EventListMapperTest.
 */
class EventListMapperTest extends AbstractServiceTestCase
{
    public function testEventListMapperToDto()
    {
        $date = new \DateTime('now');

        $eventType = new EventType();
        $eventType->setDescription('Type description');

        $eventOutcome = new EventOutcome();
        $eventOutcome->setDescription('Type description');

        $person = new Person();
        $person->setFirstName('John');
        $person->setFamilyName('Snow');

        $event = new Event();
        $event->setId(1);
        $event->setEventDate($date);
        $event->setShortDescription('Short description');
        $event->setEventType($eventType);
        $event->setEventOutcome($eventOutcome);
        $event->setCreatedBy($person);

        $eventListMapper = new EventListMapper();

        $dto = $eventListMapper->toDto($event);
        $this->assertInstanceOf(EventDto::class, $dto);
        $this->assertSame($event->getId(), $dto->getId());
        $this->assertSame(DateTimeApiFormat::dateTime($date), $dto->getDate());
        $this->assertSame($event->getShortDescription(), $dto->getDescription());
        $this->assertSame($event->getEventType()->getDescription(), $dto->getType());
        $this->assertSame($event->getEventOutcome()->getDescription(), $dto->getEventOutcomeDescription());
        $this->assertSame($event->getCreatedBy()->getDisplayName(), $dto->getAddedByName());
    }
}
