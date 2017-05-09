<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * DirectDebit.
 *
 * @ORM\Table(name="direct_debit")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\DirectDebitRepository")
 */
class DirectDebit extends Entity
{
    use CommonIdentityTrait;

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
     * @var \DvsaEntities\Entity\Organisation
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Organisation")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     */
    private $organisation;

    /**
     * @var string
     *
     * @ORM\Column(name="mandate_reference", type="string", length=50, nullable=false, unique=true)
     */
    private $mandateReference;
    /**
     * @var string
     *
     * @ORM\Column(name="unique_identifier", type="string", length=8, nullable=true)
     */
    private $uniqueIdentifier;
    /**
     * @var \DvsaEntities\Entity\DirectDebitStatus
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\DirectDebitStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="slots", type="integer", length=50, nullable=false)
     */
    private $slots;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="setup_date", type="datetime", nullable=false)
     */
    private $setupDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="next_collection_date", type="date", nullable=false)
     */
    private $nextCollectionDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_increment_date", type="date", nullable=true)
     */
    private $lastIncrementDate;
    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive = false;

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param $isActive
     *
     * @return $this
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Set person.
     *
     * @param \DvsaEntities\Entity\Person $person
     *
     * @return $this
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
     * Set organisation.
     *
     * @param \DvsaEntities\Entity\Organisation $organisation
     *
     * @return $this
     */
    public function setOrganisation(\DvsaEntities\Entity\Organisation $organisation = null)
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
     * @param \DateTime $nextCollectionDate
     *
     * @return $this
     */
    public function setNextCollectionDate($nextCollectionDate)
    {
        $this->nextCollectionDate = $nextCollectionDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getNextCollectionDate()
    {
        return $this->nextCollectionDate;
    }

    /**
     * @param \DateTime $lastIncrementDate
     *
     * @return $this
     */
    public function setLastIncrementDate($lastIncrementDate)
    {
        $this->lastIncrementDate = $lastIncrementDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastIncrementDate()
    {
        return $this->lastIncrementDate;
    }

    /**
     * @param string $mandateReference
     *
     * @return $this
     */
    public function setMandateReference($mandateReference)
    {
        $this->mandateReference = $mandateReference;
        if (is_string($mandateReference)) {
            $this->setUniqueIdentifier(substr($mandateReference, -8));
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getMandateReference()
    {
        return $this->mandateReference;
    }

    /**
     * @param \DvsaEntities\Entity\DirectDebitStatus $status
     *
     * @return $this
     */
    public function setStatus(\DvsaEntities\Entity\DirectDebitStatus $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\DirectDebitStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param \DateTime $setupDate
     *
     * @return $this
     */
    public function setSetupDate($setupDate)
    {
        $this->setupDate = $setupDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSetupDate()
    {
        return $this->setupDate;
    }

    /**
     * @param int $slots
     *
     * @return $this
     */
    public function setSlots($slots)
    {
        $this->slots = $slots;

        return $this;
    }

    /**
     * @return int
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @return string
     */
    public function getUniqueIdentifier()
    {
        return $this->uniqueIdentifier;
    }

    /**
     * @param $uniqueIdentifier
     *
     * @return $this
     */
    public function setUniqueIdentifier($uniqueIdentifier)
    {
        $this->uniqueIdentifier = $uniqueIdentifier;

        return $this;
    }
}
