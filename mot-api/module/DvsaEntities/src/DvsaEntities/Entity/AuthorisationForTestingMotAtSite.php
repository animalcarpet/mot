<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaCommon\Enum\AuthorisationForTestingMotAtSiteStatusCode;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * AuthorisationForTestingMotAtSite.
 *
 * @ORM\Table(name="auth_for_testing_mot_at_site")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\AuthorisationForTestingMotAtSiteRepository")
 */
class AuthorisationForTestingMotAtSite extends Entity
{
    use CommonIdentityTrait;

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
     * @var \DvsaEntities\Entity\VehicleClass
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\VehicleClass")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="vehicle_class_id", referencedColumnName="id")
     * })
     */
    private $vehicleClass;

    /**
     * @var \DvsaEntities\Entity\AuthorisationForTestingMotAtSiteStatus
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\AuthorisationForTestingMotAtSiteStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var \DvsaEntities\Entity\Site
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Site", inversedBy="authorisationsForTestingMotAtSite")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="site_id", referencedColumnName="id")
     * })
     */
    private $site;

    /**
     * Set validFrom.
     *
     * @param \DateTime $validFrom
     *
     * @return AuthorisationForTestingMotAtSite
     */
    public function setValidFrom($validFrom)
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
     * @return AuthorisationForTestingMotAtSite
     */
    public function setExpiryDate($expiryDate)
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
     * Set vehicleClass.
     *
     * @param \DvsaEntities\Entity\VehicleClass $vehicleClass
     *
     * @return AuthorisationForTestingMotAtSite
     */
    public function setVehicleClass(VehicleClass $vehicleClass = null)
    {
        $this->vehicleClass = $vehicleClass;

        return $this;
    }

    /**
     * Get vehicleClass.
     *
     * @return \DvsaEntities\Entity\VehicleClass
     */
    public function getVehicleClass()
    {
        return $this->vehicleClass;
    }

    /**
     * Set status.
     *
     * @param AuthorisationForTestingMotAtSiteStatus $status
     *
     * @return $this
     */
    public function setStatus(AuthorisationForTestingMotAtSiteStatus $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return AuthorisationForTestingMotAtSiteStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param \DvsaEntities\Entity\Site $site
     *
     * @return AuthorisationForTestingMotAtSite
     */
    public function setSite(Site $site = null)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @return bool
     */
    public function isApproved()
    {
        return $this->getStatus()->getCode() === AuthorisationForTestingMotAtSiteStatusCode::APPROVED;
    }
}
