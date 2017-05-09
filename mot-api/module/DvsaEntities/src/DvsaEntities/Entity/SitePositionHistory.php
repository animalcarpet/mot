<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * SitePositionHistory.
 *
 * @ORM\Table(name="site_position_history", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\SitePositionHistoryRepository")
 */
class SitePositionHistory extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="\DvsaEntities\Entity\Person")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $person;

    /**
     * @var \DvsaEntities\Entity\SiteBusinessRole
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\SiteBusinessRole")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_role_id", referencedColumnName="id")
     * })
     */
    private $role;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Site", inversedBy="positions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $site;

    /**
     * @var int
     *
     * @ORM\Column(name="status_id", type="integer", nullable=false)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="actioned_on", type="datetime", nullable=false)
     */
    private $actionedOn;

    /**
     * Constructs history item based on existing position.
     *
     * @param SiteBusinessRoleMap $sitePosition
     * @param                     $status
     *
     * @return SitePositionHistory
     */
    public static function fromSitePosition(SiteBusinessRoleMap $sitePosition, $status = null)
    {
        $historyItem = new self();
        $historyItem->person = $sitePosition->getPerson();
        $historyItem->role = $sitePosition->getSiteBusinessRole();
        $historyItem->site = $sitePosition->getSite();
        $historyItem->status = $status ?: $sitePosition->getBusinessRoleStatus()->getId();
        $historyItem->actionedOn = new \DateTime();

        return $historyItem;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setActionedOn($confirmedOn)
    {
        $this->actionedOn = $confirmedOn;

        return $this;
    }

    public function getActionedOn()
    {
        return $this->actionedOn;
    }
}
