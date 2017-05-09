<?php

namespace DvsaMotApi\Helper;

use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\EventPersonMapRepository;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaEventApi\Service\EventService;
use DvsaEntities\Entity\EventPersonMap;
use DvsaEntities\Entity\Event;

class TesterQualificationStatusChangeEventHelper
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
     * @param Person $person
     * @param string $group
     *
     * @return EventPersonMap
     */
    public function create(Person $person, $group)
    {
        if ($group === VehicleClassGroupCode::BIKES) {
            return $this->createEventForGroupA($person);
        } elseif ($group === VehicleClassGroupCode::CARS_ETC) {
            return $this->createEventForGroupB($person);
        }

        throw new \InvalidArgumentException('Group "'.$group.'"  not found.');
    }

    /**
     * @param Person $person
     *
     * @return EventPersonMap
     *
     * @throws \Exception
     */
    private function createEventForGroupA(Person $person)
    {
        $event = $this->eventService->addEvent(
            EventTypeCode::GROUP_A_TESTER_QUALIFICATION,
            sprintf(EventDescription::TESTER_QUALIFICATION_STATUS_CHANGE, VehicleClassGroupCode::BIKES, $this->getDisplayName()),
            $this->dateTimeHolder->getCurrent(true)
        );

        return $this->createEventPersonMap($person, $event);
    }

    /**
     * @param Person $person
     *
     * @return EventPersonMap
     *
     * @throws \Exception
     */
    private function createEventForGroupB(Person $person)
    {
        $event = $this->eventService->addEvent(
            EventTypeCode::GROUP_B_TESTER_QUALIFICATION,
            sprintf(EventDescription::TESTER_QUALIFICATION_STATUS_CHANGE, VehicleClassGroupCode::CARS_ETC, $this->getDisplayName()),
            $this->dateTimeHolder->getCurrent(true)
        );

        return $this->createEventPersonMap($person, $event);
    }

    /**
     * @param Person $person
     * @param Event  $event
     *
     * @return EventPersonMap
     */
    private function createEventPersonMap(Person $person, Event $event)
    {
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
