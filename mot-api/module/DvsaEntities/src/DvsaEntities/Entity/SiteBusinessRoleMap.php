<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * SiteBusinessRoleMap.
 *
 * @ORM\Table(
 *  name="site_business_role_map",
 *  indexes={
 *      @ORM\Index(name="fk_site_role_map_site", columns={"site_id"}),
 *      @ORM\Index(name="fk_site_role_map_person", columns={"person_id"}),
 *      @ORM\Index(name="fk_site_role_map_status", columns={"status_id"}),
 *      @ORM\Index(name="fk_site_business_role_map", columns={"site_business_role_id"}),
 *      @ORM\Index(name="created_by", columns={"created_by"}),
 *      @ORM\Index(name="last_updated_by", columns={"last_updated_by"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\SiteBusinessRoleMapRepository")
 */
class SiteBusinessRoleMap extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="status_changed_on", type="datetime", nullable=true)
     */
    private $statusChangedOn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=true)
     */
    private $validFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiry_date", type="datetime", nullable=true)
     */
    private $expiryDate;

    /**
     * @var \DvsaEntities\Entity\SiteBusinessRole
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\SiteBusinessRole")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_business_role_id", referencedColumnName="id")
     * })
     */
    private $siteBusinessRole;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person", inversedBy="siteBusinessRoleMaps")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $person;

    /**
     * @var \DvsaEntities\Entity\Site
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Site", inversedBy="positions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $site;

    /**
     * @var \DvsaEntities\Entity\BusinessRoleStatus
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\BusinessRoleStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $businessRoleStatus;

    /**
     * Set statusChangedOn.
     *
     * @param \DateTime $statusChangedOn
     *
     * @return SiteBusinessRoleMap
     */
    public function setStatusChangedOn(\DateTime $statusChangedOn)
    {
        $this->statusChangedOn = $statusChangedOn;

        return $this;
    }

    /**
     * Get statusChangedOn.
     *
     * @return \DateTime
     */
    public function getStatusChangedOn()
    {
        return $this->statusChangedOn;
    }

    /**
     * Set validFrom.
     *
     * @param \DateTime $validFrom
     *
     * @return SiteBusinessRoleMap
     */
    public function setValidFrom(\DateTime $validFrom)
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * Get validFrom.
     *
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * Set expiryDate.
     *
     * @param \DateTime $expiryDate
     *
     * @return SiteBusinessRoleMap
     */
    public function setExpiryDate(\DateTime $expiryDate)
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    /**
     * Get expiryDate.
     *
     * @return \DateTime
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set siteBusinessRole.
     *
     * @param \DvsaEntities\Entity\SiteBusinessRole $siteBusinessRole
     *
     * @return SiteBusinessRoleMap
     */
    public function setSiteBusinessRole(\DvsaEntities\Entity\SiteBusinessRole $siteBusinessRole = null)
    {
        $this->siteBusinessRole = $siteBusinessRole;

        return $this;
    }

    /**
     * Get siteBusinessRole.
     *
     * @return \DvsaEntities\Entity\SiteBusinessRole
     */
    public function getSiteBusinessRole()
    {
        return $this->siteBusinessRole;
    }

    /**
     * Set person.
     *
     * @param \DvsaEntities\Entity\Person $person
     *
     * @return SiteBusinessRoleMap
     */
    public function setPerson(\DvsaEntities\Entity\Person $person = null)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Get person.
     *
     * @return \DvsaEntities\Entity\Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * Set site.
     *
     * @param \DvsaEntities\Entity\Site $site
     *
     * @return SiteBusinessRoleMap
     */
    public function setSite(\DvsaEntities\Entity\Site $site = null)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site.
     *
     * @return \DvsaEntities\Entity\Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set businessRoleStatus.
     *
     * @param \DvsaEntities\Entity\BusinessRoleStatus $businessRoleStatus
     *
     * @return SiteBusinessRoleMap
     */
    public function setBusinessRoleStatus(\DvsaEntities\Entity\BusinessRoleStatus $businessRoleStatus = null)
    {
        $this->businessRoleStatus = $businessRoleStatus;

        return $this;
    }

    /**
     * Get businessRoleStatus.
     *
     * @return \DvsaEntities\Entity\BusinessRoleStatus
     */
    public function getBusinessRoleStatus()
    {
        return $this->businessRoleStatus;
    }
}
