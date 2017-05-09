<?php

namespace PersonApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\EventCategoryCode;
use DvsaEntities\Entity\EventPersonMap;
use DvsaEntities\Entity\Event;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaEventApi\Service\EventService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEventApi\Service\RecordEventResult;

class PersonEventService
{
    /**
     * @var EventService
     */
    private $eventService;

    /**
     * @var AuthorisationServiceInterface
     */
    private $authService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var PersonRepository
     */
    private $personRepository;

    public function __construct(
        EventService $eventService,
        AuthorisationServiceInterface $authService,
        EntityManager $entityManager,
        PersonRepository $personRepository
    ) {
        $this->eventService = $eventService;
        $this->authService = $authService;
        $this->entityManager = $entityManager;
        $this->personRepository = $personRepository;
    }

    /**
     * @param int   $personId
     * @param array $data
     *
     * @return RecordEventResult
     *
     * @throws NotFoundException
     * @throws \Exception
     */
    public function create($personId, array $data)
    {
        $this->authService->assertGranted(PermissionInSystem::EVENT_CREATE);
        $this->assertEventCategory($data);

        /** @var Person $person */
        $person = $this->getPersonEntity($personId);

        $this->entityManager->beginTransaction();

        try {
            /** @var Event $event */
            $event = $this->eventService->recordManualEvent($data);
            $this->createEventPersonMap($person, $event);
            $this->entityManager->commit();

            return new RecordEventResult($event);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * @param Person $person
     * @param Event  $event
     *
     * @return bool
     */
    private function createEventPersonMap(Person $person, Event $event)
    {
        $eventPersonMap = (new EventPersonMap())
            ->setPerson($person)
            ->setEvent($event);

        $this->entityManager->persist($eventPersonMap);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Ensures that the category being passed in data is the right one for the entity.
     *
     * @param array $data
     *
     * @return bool
     *
     * @throws \InvalidArgumentException if wrong category supplied
     */
    private function assertEventCategory(array $data)
    {
        if (isset($data['eventCategoryCode']) && $data['eventCategoryCode'] == EventCategoryCode::NT_EVENTS) {
            return true;
        }
        throw new \InvalidArgumentException('Category not recognised');
    }

    /**
     * Retrieves the person entity from the DB.
     *
     * @param int $personId
     *
     * @return Person
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    private function getPersonEntity($personId)
    {
        // PersonRepository
        $person = $this->personRepository->find($personId);
        if (!$person instanceof Person) {
            throw new NotFoundException('Unable to find person with id '.$personId);
        }

        return $person;
    }
}
