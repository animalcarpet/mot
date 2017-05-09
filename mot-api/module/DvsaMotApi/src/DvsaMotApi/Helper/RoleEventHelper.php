<?php

namespace DvsaMotApi\Helper;

use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonSystemRole;
use DvsaEntities\Repository\EventPersonMapRepository;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\EventTypeCode;
use DvsaEventApi\Service\EventService;
use DvsaEntities\Entity\EventPersonMap;

class RoleEventHelper
{
    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var DateTimeHolder
     */
    private $dateTimeHolder;

    /**
     * @var EventService
     */
    private $eventService;

    /**
     * @var EventPersonMapRepository
     */
    private $eventPersonMapRepository;

    /**
     * @param MotIdentityProviderInterface $identityProvider
     * @param EventService                 $eventService
     * @param EventPersonMapRepository     $eventPersonMapRepository
     * @param DateTimeHolder               $dateTimeHolder
     */
    public function __construct(
        MotIdentityProviderInterface $identityProvider,
        EventService $eventService,
        EventPersonMapRepository $eventPersonMapRepository,
        DateTimeHolder $dateTimeHolder
    ) {
        $this->identityProvider = $identityProvider;
        $this->eventService = $eventService;
        $this->eventPersonMapRepository = $eventPersonMapRepository;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    /**
     * @param Person           $person
     * @param PersonSystemRole $role
     *
     * @return EventPersonMap
     */
    public function createRemoveRoleEvent(Person $person, PersonSystemRole $role)
    {
        $description = $this->getDescription($role, EventDescription::DVSA_ROLE_ASSOCIATION_REMOVE);

        return $this->create($person, $description);
    }

    /**
     * @param Person           $person
     * @param PersonSystemRole $role
     *
     * @return EventPersonMap
     */
    public function createAssignRoleEvent(Person $person, PersonSystemRole $role)
    {
        $description = $this->getDescription($role, EventDescription::DVSA_ROLE_ASSOCIATION_ASSIGN);

        return $this->create($person, $description);
    }

    /**
     * @param PersonSystemRole $role
     * @param string           $eventDescription
     *
     * @return string
     */
    private function getDescription(PersonSystemRole $role, $eventDescription)
    {
        return sprintf($eventDescription, $role->getFullName(), $this->getDisplayName());
    }

    /**
     * @param Person $person
     * @param string $description
     *
     * @return EventPersonMap
     *
     * @throws \Exception
     */
    private function create(Person $person, $description)
    {
        $event = $this->eventService->addEvent(
            EventTypeCode::ROLE_ASSOCIATION_CHANGE,
            $description,
            $this->dateTimeHolder->getCurrent(true)
        );

        $eventMap = (new EventPersonMap())
            ->setEvent($event)
            ->setPerson($person);

        $this->eventPersonMapRepository->save($eventMap);

        return $eventMap;
    }

    /**
     * @return string
     */
    private function getDisplayName()
    {
        return $this->identityProvider->getIdentity()->getDisplayName();
    }
}
