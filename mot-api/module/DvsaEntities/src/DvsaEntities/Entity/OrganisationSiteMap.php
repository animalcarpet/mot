<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * OrganisationSiteMap.
 *
 * @ORM\Table(
 *  name="organisation_site_map",
 *  options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"},
 *  indexes={
 *      @ORM\Index(name="fk_organisation_site_map_organisation_id", columns={"organisation_id"}),
 *      @ORM\Index(name="fk_organisation_site_map_site_id", columns={"site_id"}),
 *      @ORM\Index(name="fk_organisation_site_map_organisation_site_status", columns={"status_id"}),
 *      @ORM\Index(name="fk_organisation_site_map_person_created_by", columns={"created_by"}),
 *      @ORM\Index(name="fk_organisation_site_map_person_last_updated_by", columns={"last_updated_by"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\OrganisationSiteMapRepository")
 */
class OrganisationSiteMap extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'OrganisationSiteMap';

    /**
     * @var \DvsaEntities\Entity\Organisation
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Organisation", fetch="LAZY")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     * })
     */
    private $organisation;

    /**
     * @var \DvsaEntities\Entity\Site
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Site", fetch="LAZY", inversedBy="associationsWithAe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $site;

    /**
     * @var string
     *
     * @ORM\Column(name="trading_name", type="string", nullable=false)
     */
    private $tradingName;

    /**
     * @var \DvsaEntities\Entity\OrganisationSiteStatus
     *
     * @ORM\ManyToOne(
     *  targetEntity="DvsaEntities\Entity\OrganisationSiteStatus",
     *  fetch="LAZY"
     *  )
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="status_changed_on", type="datetimemicro", nullable=false)
     */
    protected $statusChangedOn;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="start_date", type="datetimemicro", nullable=false)
     */
    protected $startDate;

    /**
     * @var \DateTime|null
     * @ORM\Column(name="end_date", type="datetimemicro", nullable=false)
     */
    protected $endDate;

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
     * @return $this
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;

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
     * @return $this
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return string
     */
    public function getTradingName()
    {
        return $this->tradingName;
    }

    /**
     * @param string $tradingName
     *
     * @return $this
     */
    public function setTradingName($tradingName)
    {
        $this->tradingName = $tradingName;

        return $this;
    }

    /**
     * @return OrganisationSiteStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param OrganisationSiteStatus $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getStatusChangedOn()
    {
        return $this->statusChangedOn;
    }

    /**
     * @param \DateTime|null $statusChangedOn
     *
     * @return $this
     */
    public function setStatusChangedOn($statusChangedOn)
    {
        $this->statusChangedOn = $statusChangedOn;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime|null $startDate
     *
     * @return $this
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime|null $endDate
     *
     * @return $this
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }
}
