<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * EventSiteMap.
 *
 * @ORM\Table(
 *  name="event_site_map",
 *  indexes={
 *      @ORM\Index(name="ix_event_site_map_event_id", columns={"event_id"}),
 *      @ORM\Index(name="ix_event_site_map_site_id", columns={"site_id"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\EventSiteMapRepository")
 */
class EventSiteMap extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\Event
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Event", inversedBy="eventSiteMaps")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var \DvsaEntities\Entity\Site
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Site")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $site;

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
     * @return EventSiteMap
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param Site $site
     *
     * @return EventSiteMap
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }
}
