<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * EventOrganisationMap.
 *
 * @ORM\Table(
 *  name="event_organisation_map",
 *  indexes={
 *      @ORM\Index(name="ix_event_organisation_map_event_id", columns={"event_id"}),
 *      @ORM\Index(name="ix_event_organisation_map_organisation_id", columns={"organisation_id"}),
 *      @ORM\Index(name="ix_event_organisation_map_created_by", columns={"created_by"}),
 *      @ORM\Index(name="ix_event_organisation_map_last_updated_by", columns={"last_updated_by"})})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\EventOrganisationMapRepository")
 */
class EventOrganisationMap extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\Event
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Event", inversedBy="eventOrganisationMaps")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var \DvsaEntities\Entity\Organisation
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Organisation")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     * })
     */
    private $organisation;

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     *
     * @return EventOrganisationMap
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param Organisation $organisation
     *
     * @return EventOrganisationMap
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

        return $this;
    }
}
