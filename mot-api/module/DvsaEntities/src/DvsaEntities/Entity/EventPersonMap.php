<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * EventPersonMap.
 *
 * @ORM\Table(
 *  name="event_person_map",
 *  indexes={
 *      @ORM\Index(name="ix_event_person_map_event_id", columns={"event_id"}),
 *      @ORM\Index(name="ix_event_person_map_person_id", columns={"person_id"}),
 *      @ORM\Index(name="ix_event_person_map_created_by", columns={"created_by"}),
 *      @ORM\Index(name="ix_event_person_map_last_updated_by", columns={"last_updated_by"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\EventPersonMapRepository")
 */
class EventPersonMap extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\Event
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Event", inversedBy="eventPersonMaps")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $person;

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
     * @return EventPersonMap
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return EventPersonMap
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }
}
