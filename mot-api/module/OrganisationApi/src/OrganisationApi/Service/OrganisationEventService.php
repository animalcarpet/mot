<?php

namespace OrganisationApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\EventCategoryCode;
use DvsaEntities\Entity\EventOrganisationMap;
use DvsaEntities\Entity\Event;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaEventApi\Service\EventService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEventApi\Service\RecordEventResult;

class OrganisationEventService
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
     * @var OrganisationRepository
     */
    private $organisationRepository;

    public function __construct(
        EventService $eventService,
        AuthorisationServiceInterface $authService,
        EntityManager $entityManager,
        OrganisationRepository $organisationRepository
    ) {
        $this->eventService = $eventService;
        $this->authService = $authService;
        $this->entityManager = $entityManager;
        $this->organisationRepository = $organisationRepository;
    }

    /**
     * @param int   $organisationId
     * @param array $data
     *
     * @return Event
     *
     * @throws NotFoundException
     * @throws \Exception
     */
    public function create($organisationId, array $data)
    {
        $this->authService->assertGranted(PermissionInSystem::EVENT_CREATE);
        $this->assertEventCategory($data);

        /** @var Organisation $organisation */
        $organisation = $this->getOrganisationEntity($organisationId);

        $this->entityManager->beginTransaction();

        try {
            /** @var Event $event */
            $event = $this->eventService->recordManualEvent($data);

            $this->createEventOrganisationMap($organisation, $event);
            $this->entityManager->commit();

            return new RecordEventResult($event);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * @param Organisation $organisation
     * @param Event        $event
     *
     * @return bool
     */
    private function createEventOrganisationMap(Organisation $organisation, Event $event)
    {
        $eventOrganisationMap = (new EventOrganisationMap())
            ->setOrganisation($organisation)
            ->setEvent($event);

        $this->entityManager->persist($eventOrganisationMap);
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
        if (isset($data['eventCategoryCode']) && $data['eventCategoryCode'] == EventCategoryCode::AE_EVENT) {
            return true;
        }
        throw new \InvalidArgumentException('Category not recognised');
    }

    /**
     * Retrieves the organisation entity from the DB.
     *
     * @param int $organisationId
     *
     * @return Organisation
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    private function getOrganisationEntity($organisationId)
    {
        // OrganisationRepository
        $organisation = $this->organisationRepository->find($organisationId);
        if (!$organisation instanceof Organisation) {
            throw new NotFoundException('Unable to find organisation with id '.$organisationId);
        }

        return $organisation;
    }
}
