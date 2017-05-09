<?php

namespace DvsaEventApi\Service\Mapper;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Event\EventDto;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;
use DvsaEntities\Entity\Event;

/**
 * Class EventListMapper.
 */
class EventListMapper extends AbstractApiMapper
{
    /**
     * @param Event $event
     *
     * @return EventDto
     */
    public function toDto($event)
    {
        $addedByName = $event->getCreatedBy() ? $event->getCreatedBy()->getDisplayName() : null;
        $eventOutcomeDescription = $event->getEventOutcome() ? $event->getEventOutcome()->getDescription() : null;

        $dto = new EventDto();
        $dto
            ->setId($event->getId())
            ->setDate(DateTimeApiFormat::dateTime($event->getEventDate()))
            ->setDescription($event->getShortDescription())
            ->setType($event->getEventType()->getDescription())
            ->setAddedByName($addedByName)
            ->setEventOutcomeDescription($eventOutcomeDescription);

        return $dto;
    }
}
