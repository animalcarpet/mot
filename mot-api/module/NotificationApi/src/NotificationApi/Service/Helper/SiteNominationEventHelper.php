<?php

namespace NotificationApi\Service\Helper;

use DvsaEntities\Repository\EventSiteMapRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\EventTypeCode;
use DvsaEventApi\Service\EventService;
use DvsaEntities\Entity\EventSiteMap;
use DvsaEntities\Entity\Notification;

class SiteNominationEventHelper
{
    private $dateTimeHolder;

    private $eventService;

    private $eventSiteMapRepository;

    private $siteRepository;

    public function __construct(
        EventService $eventService,
        EventSiteMapRepository $eventSiteMapRepository,
        SiteRepository $siteRepository,
        DateTimeHolder $dateTimeHolder
    ) {
        $this->eventService = $eventService;
        $this->eventSiteMapRepository = $eventSiteMapRepository;
        $this->siteRepository = $siteRepository;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    /**
     * @param Notification $notification
     *
     * @return EventSiteMap
     */
    public function create(Notification $notification)
    {
        $positionName = $notification->getFieldValue('positionName');
        $siteName = $notification->getFieldValue('siteName');
        $siteId = $notification->getFieldValue('siteOrOrganisationId');
        $person = $notification->getRecipient();
        $description = sprintf(
            EventDescription::ROLE_NOMINATION_ACCEPT,
            $positionName,
            $person->getDisplayName(),
            $person->getUsername(),
            $siteId,
            $siteName
        );

        $event = $this->eventService->addEvent(
            EventTypeCode::ROLE_ASSOCIATION_CHANGE,
            $description,
            $this->dateTimeHolder->getCurrent(true)
        );

        $site = $this->siteRepository->getBySiteNumber($siteId);
        $eventMap = (new EventSiteMap())
            ->setEvent($event)
            ->setSite($site);

        $this->eventSiteMapRepository->save($eventMap);

        return $eventMap;
    }
}
