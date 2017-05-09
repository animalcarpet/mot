<?php

namespace DvsaEntities\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Event.
 *
 * @ORM\Table(
 *  name="event",
 *  indexes={
 *      @ORM\Index(name="ix_event_type_id", columns={"event_type_id"}),
 *      @ORM\Index(name="ix_event_outcome_id", columns={"event_outcome_id"}),
 *      @ORM\Index(name="ix_comment_id", columns={"comment_id"}),
 *      @ORM\Index(name="ix_created_by", columns={"created_by"}),
 *      @ORM\Index(name="ix_last_updated_by", columns={"last_updated_by"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\EventRepository")
 */
class Event extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=100, nullable=false)
     */
    private $shortDescription;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_manual_event", type="boolean", nullable=false)
     */
    private $isManualEvent = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="event_date", type="datetime", nullable=false)
     */
    private $eventDate = 'CURRENT_TIMESTAMP';

    /**
     * @var \DvsaEntities\Entity\Comment
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Comment")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="comment_id", referencedColumnName="id")
     * })
     */
    private $comment;

    /**
     * @var \DvsaEntities\Entity\EventOutcome
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EventOutcome", inversedBy="events")
     * @ORM\JoinColumn(name="event_outcome_id", referencedColumnName="id")
     */
    private $eventOutcome;

    /**
     * @var \DvsaEntities\Entity\EventType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EventType")
     * @ORM\JoinColumn(name="event_type_id", referencedColumnName="id")
     */
    private $eventType;

    /**
     * @var EventOrganisationMap[]
     *
     * @ORM\OneToMany(targetEntity="DvsaEntities\Entity\EventOrganisationMap", mappedBy="event")
     */
    private $eventOrganisationMaps;

    /**
     * @var EventSiteMap[]
     *
     * @ORM\OneToMany(targetEntity="DvsaEntities\Entity\EventSiteMap", mappedBy="event")
     */
    private $eventSiteMaps;

    /**
     * @var EventPersonMap[]
     * @ORM\OneToMany(targetEntity="DvsaEntities\Entity\EventPersonMap", mappedBy="event")
     */
    private $eventPersonMaps;

    public function __construct()
    {
        $this->eventOrganisationMaps = new ArrayCollection();
        $this->eventSiteMaps = new ArrayCollection();
        $this->eventPersonMaps = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @param string $shortDescription
     *
     * @return Event
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * @return int
     */
    public function getIsManualEvent()
    {
        return $this->isManualEvent;
    }

    /**
     * @param bool $isManualEvent
     *
     * @return Event
     */
    public function setIsManualEvent($isManualEvent)
    {
        $this->isManualEvent = $isManualEvent;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEventDate()
    {
        return $this->eventDate;
    }

    /**
     * @param \DateTime $eventDate
     *
     * @return Event
     */
    public function setEventDate($eventDate)
    {
        $this->eventDate = $eventDate;

        return $this;
    }

    /**
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param Comment $comment
     *
     * @return Event
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return EventOutcome
     */
    public function getEventOutcome()
    {
        return $this->eventOutcome;
    }

    /**
     * @param EventOutcome $eventOutcome
     *
     * @return Event
     */
    public function setEventOutcome($eventOutcome)
    {
        $this->eventOutcome = $eventOutcome;

        return $this;
    }

    /**
     * @return EventType
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * @param EventType $eventType
     *
     * @return Event
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function addPersonMap(EventPersonMap $eventPersonMap)
    {
        $this->eventPersonMaps->add($eventPersonMap);

        return $this;
    }
}
