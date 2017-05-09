<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * PersonSystemRoleMap.
 *
 * @ORM\Table(name="person_system_role_map")
 * @ORM\Entity(repositoryClass="\DvsaEntities\Repository\PersonSystemRoleMapRepository")
 */
class PersonSystemRoleMap extends Entity
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
     * @var \DvsaEntities\Entity\PersonSystemRole
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\PersonSystemRole")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_system_role_id", referencedColumnName="id")
     * })
     */
    private $personSystemRole;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person", inversedBy="personSystemRoleMap")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $person;

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
     * @return PersonSystemRoleMap
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
     * @param \DateTime $vaildFrom
     *
     * @return PersonSystemRoleMap
     */
    public function setValidFrom(\DateTime $vaildFrom)
    {
        $this->validFrom = $vaildFrom;

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
     * @return PersonSystemRoleMap
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
     * Set personSystemRole.
     *
     * @param \DvsaEntities\Entity\PersonSystemRole $personSystemRole
     *
     * @return PersonSystemRoleMap
     */
    public function setPersonSystemRole(PersonSystemRole $personSystemRole = null)
    {
        $this->personSystemRole = $personSystemRole;

        return $this;
    }

    /**
     * Get personSystemRole.
     *
     * @return \DvsaEntities\Entity\PersonSystemRole
     */
    public function getPersonSystemRole()
    {
        return $this->personSystemRole;
    }

    /**
     * Set person.
     *
     * @param \DvsaEntities\Entity\Person $person
     *
     * @return PersonSystemRoleMap
     */
    public function setPerson(Person $person = null)
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
     * Set businessRoleStatus.
     *
     * @param \DvsaEntities\Entity\BusinessRoleStatus $businessRoleStatus
     *
     * @return PersonSystemRoleMap
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
}
