<?php

namespace DvsaEventApi\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaCommon\Date\DateTimeHolder;
use DvsaEntities\Entity\Event;
use DvsaEntities\Entity\EventPersonMap;
use DvsaEntities\Repository\PersonRepository;

class EventPersonCreationService
{
    private $personRepository;

    private $eventTypeRepository;

    private $entityManager;

    public function __construct(PersonRepository $personRepository,
                                EntityRepository $eventTypeRepository,
                                EntityManager $entityManager)
    {
        $this->personRepository = $personRepository;
        $this->eventTypeRepository = $eventTypeRepository;
        $this->entityManager = $entityManager;
    }

    public function createPersonEvent($personId, $eventCode, $description)
    {
        $person = $this->personRepository->find($personId);

        if (is_null($person)) {
            throw new \Exception('Unable to find person with id: ' . $personId);
        }

        $eventType = $this->eventTypeRepository->findOneBy(['code' => $eventCode]);

        if (is_null($eventType)) {
            throw new \Exception('Unable to find event type with code: ' . $eventCode);
        }

        $dateTimeHolder = new DateTimeHolder();

        $event = new Event();
        $event
            ->setEventType($eventType)
            ->setIsManualEvent(false)
            ->setShortDescription($description)
            ->setEventDate($dateTimeHolder->getCurrent(true));

        $personEvent = new EventPersonMap();
        $personEvent
            ->setPerson($person)
            ->setEvent($event);

        $this->entityManager->beginTransaction();

        try {
            $this->entityManager->persist($event);
            $this->entityManager->persist($personEvent);
            $this->entityManager->flush();
            $this->entityManager->commit();
            return new RecordEventResult($event);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

    }
}