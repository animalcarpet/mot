<?php

namespace NotificationApi\Service\Helper;

use DvsaEntities\Repository\EventOrganisationMapRepository;
use DvsaEntities\Repository\AuthorisationForAuthorisedExaminerRepository;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\EventTypeCode;
use DvsaEventApi\Service\EventService;
use DvsaEntities\Entity\EventOrganisationMap;
use DvsaEntities\Entity\Notification;

class OrganisationNominationEventHelper
{
    private $dateTimeHolder;

    private $eventService;

    private $eventOrganisationMapRepository;

    private $authRepository;

    public function __construct(
        EventService $eventService,
        EventOrganisationMapRepository $eventOrganisationMapRepository,
        AuthorisationForAuthorisedExaminerRepository $authRepository,
        DateTimeHolder $dateTimeHolder
    ) {
        $this->eventService = $eventService;
        $this->eventOrganisationMapRepository = $eventOrganisationMapRepository;
        $this->authRepository = $authRepository;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    /**
     * @param Notification $notification
     *
     * @return EventOrganisationMap
     */
    public function create(Notification $notification)
    {
        $positionName = $notification->getFieldValue('positionName');
        $organisationName = $notification->getFieldValue('organisationName');
        $organisationId = $notification->getFieldValue('siteOrOrganisationId');
        $person = $notification->getRecipient();
        $description = sprintf(
            EventDescription::ROLE_NOMINATION_ACCEPT,
            $positionName,
            $person->getDisplayName(),
            $person->getUsername(),
            $organisationId,
            $organisationName
        );

        $event = $this->eventService->addEvent(
            EventTypeCode::ROLE_ASSOCIATION_CHANGE,
            $description,
            $this->dateTimeHolder->getCurrent(true)
        );

        $organisation = $this->authRepository->getByNumber($organisationId)->getOrganisation();
        $eventMap = (new EventOrganisationMap())
            ->setEvent($event)
            ->setOrganisation($organisation);

        $this->eventOrganisationMapRepository->save($eventMap);

        return $eventMap;
    }
}
