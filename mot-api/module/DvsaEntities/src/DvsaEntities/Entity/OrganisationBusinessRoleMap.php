<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * OrganisationBusinessRoleMap.
 *
 * @ORM\Table(
 *  name="organisation_business_role_map",
 *  indexes={
 *      @ORM\Index(name="fk_organisation_role_map_organisation", columns={"organisation_id"}),
 *      @ORM\Index(name="fk_organisation_role_map_person", columns={"person_id"}),
 *      @ORM\Index(name="fk_organisation_business_role_map", columns={"business_role_id"}),
 *      @ORM\Index(name="fk_organisation_business_role_map_status", columns={"status_id"}),
 *      @ORM\Index(name="organisation_business_role_ibfk_1", columns={"created_by"}),
 *      @ORM\Index(name="organisation_business_role_ibfk_2", columns={"last_updated_by"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\OrganisationBusinessRoleMapRepository")
 */
class OrganisationBusinessRoleMap extends Entity
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
     * @var \DvsaEntities\Entity\OrganisationBusinessRole
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\OrganisationBusinessRole")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="business_role_id", referencedColumnName="id")
     * })
     */
    private $organisationBusinessRole;

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
     * @var \DvsaEntities\Entity\Organisation
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Organisation", inversedBy="positions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     * })
     */
    private $organisation;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person", inversedBy="organisationBusinessRoleMaps")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $person;

    /**
     * Set statusChangedOn.
     *
     * @param \DateTime $statusChangedOn
     *
     * @return OrganisationBusinessRoleMap
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
     * @return OrganisationBusinessRoleMap
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
     * @return OrganisationBusinessRoleMap
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
     * Set organisationBusinessRole.
     *
     * @param \DvsaEntities\Entity\OrganisationBusinessRole $organisationBusinessRole
     *
     * @return OrganisationBusinessRoleMap
     */
    public function setOrganisationBusinessRole(OrganisationBusinessRole $organisationBusinessRole = null)
    {
        $this->organisationBusinessRole = $organisationBusinessRole;

        return $this;
    }

    /**
     * Get organisationBusinessRole.
     *
     * @return \DvsaEntities\Entity\OrganisationBusinessRole
     */
    public function getOrganisationBusinessRole()
    {
        return $this->organisationBusinessRole;
    }

    /**
     * Set businessRoleStatus.
     *
     * @param \DvsaEntities\Entity\BusinessRoleStatus $businessRoleStatus
     *
     * @return OrganisationBusinessRoleMap
     */
    public function setBusinessRoleStatus(BusinessRoleStatus $businessRoleStatus = null)
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

    /**
     * Set organisation.
     *
     * @param \DvsaEntities\Entity\Organisation $organisation
     *
     * @return OrganisationBusinessRoleMap
     */
    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get organisation.
     *
     * @return \DvsaEntities\Entity\Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * Set person.
     *
     * @param \DvsaEntities\Entity\Person $person
     *
     * @return OrganisationBusinessRoleMap
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
}
