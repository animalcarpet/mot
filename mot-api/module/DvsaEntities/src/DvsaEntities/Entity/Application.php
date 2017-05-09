<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Application.
 *
 * @ORM\Table(name="application", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_application_1_idx", columns={"status_id"}), @ORM\Index(name="fk_application_2_idx", columns={"locked_by"}), @ORM\Index(name="fk_application_5_idx", columns={"person_id"})})
 * @ORM\Entity
 */
class Application extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="application_reference", type="string", length=36, nullable=false)
     */
    private $applicationReference;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="locked_on", type="datetime", nullable=true)
     */
    private $lockedOn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="submitted_on", type="datetime", nullable=true)
     */
    private $submittedOn;

    /**
     * @var \DvsaEntities\Entity\AuthForAeStatus
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\AuthForAeStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var \DvsaEntities\Entity\Person
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Person")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="locked_by", referencedColumnName="id")
     * })
     */
    private $lockedBy;

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
     * Set applicationReference.
     *
     * @param string $applicationReference
     *
     * @return Application
     */
    public function setApplicationReference($applicationReference)
    {
        $this->applicationReference = $applicationReference;

        return $this;
    }

    /**
     * Get applicationReference.
     *
     * @return string
     */
    public function getApplicationReference()
    {
        return $this->applicationReference;
    }

    /**
     * Set lockedOn.
     *
     * @param \DateTime $lockedOn
     *
     * @return Application
     */
    public function setLockedOn($lockedOn)
    {
        $this->lockedOn = $lockedOn;

        return $this;
    }

    /**
     * Get lockedOn.
     *
     * @return \DateTime
     */
    public function getLockedOn()
    {
        return $this->lockedOn;
    }

    /**
     * Set submittedOn.
     *
     * @param \DateTime $submittedOn
     *
     * @return Application
     */
    public function setSubmittedOn($submittedOn)
    {
        $this->submittedOn = $submittedOn;

        return $this;
    }

    /**
     * Get submittedOn.
     *
     * @return \DateTime
     */
    public function getSubmittedOn()
    {
        return $this->submittedOn;
    }

    /**
     * Set status.
     *
     * @param \DvsaEntities\Entity\AuthForAeStatus $status
     *
     * @return Application
     */
    public function setStatus(\DvsaEntities\Entity\AuthForAeStatus $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return \DvsaEntities\Entity\AuthForAeStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set lockedBy.
     *
     * @param \DvsaEntities\Entity\Person $lockedBy
     *
     * @return Application
     */
    public function setLockedBy(\DvsaEntities\Entity\Person $lockedBy = null)
    {
        $this->lockedBy = $lockedBy;

        return $this;
    }

    /**
     * Get lockedBy.
     *
     * @return \DvsaEntities\Entity\Person
     */
    public function getLockedBy()
    {
        return $this->lockedBy;
    }

    /**
     * Set person.
     *
     * @param \DvsaEntities\Entity\Person $person
     *
     * @return Application
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
