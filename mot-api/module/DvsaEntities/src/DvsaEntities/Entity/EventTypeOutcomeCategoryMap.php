<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * EventTypeOutcomeCategoryMap.
 *
 * @ORM\Table(
 *  name="event_type_outcome_category_map",
 *  indexes={
 *      @ORM\Index(name="ix_event_type_outcome_category_map_event_type_id", columns={"event_type_id"}),
 *      @ORM\Index(name="ix_event_type_outcome_category_map_event_outcome_id", columns={"event_outcome_id"}),
 *      @ORM\Index(name="ix_event_type_outcome_category_map_event_category_id", columns={"event_category_id"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\EventTypeOutcomeCategoryMapRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class EventTypeOutcomeCategoryMap
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\EventCategory
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EventCategory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_category_id", referencedColumnName="id")
     * })
     */
    private $eventCategory;

    /**
     * @var \DvsaEntities\Entity\EventOutcome
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EventOutcome")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_outcome_id", referencedColumnName="id")
     * })
     */
    private $eventOutcome;

    /**
     * @var \DvsaEntities\Entity\EventType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\EventType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_type_id", referencedColumnName="id")
     * })
     */
    private $eventType;

    /**
     * @return EventCategory
     */
    public function getEventCategory()
    {
        return $this->eventCategory;
    }

    /**
     * @param EventCategory $eventCategory
     *
     * @return EventTypeOutcomeCategoryMap
     */
    public function setEventCategory($eventCategory)
    {
        $this->eventCategory = $eventCategory;

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
     * @return EventTypeOutcomeCategoryMap
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
     * @return EventTypeOutcomeCategoryMap
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;

        return $this;
    }
}
