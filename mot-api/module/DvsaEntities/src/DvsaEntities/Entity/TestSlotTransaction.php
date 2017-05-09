<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * TestSlotTransaction.
 *
 * @ORM\Table(name="test_slot_transaction", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\TestSlotTransactionRepository")
 */
class TestSlotTransaction extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="slots", type="integer", nullable=false)
     */
    private $slots;

    /**
     * @var int
     *
     * @ORM\Column(name="slots_after", type="integer", nullable=false)
     */
    private $slotsAfter;

    /**
     * @var int
     *
     * @ORM\Column(name="real_slots", type="integer", nullable=false)
     */
    private $realSlots;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=50, nullable=false)
     */
    private $state;
    /**
     * @var string
     *
     * @ORM\Column(name="sales_reference", type="string", length=55, nullable=false)
     */
    private $salesReference;
    /**
     * @var string
     *
     * @ORM\Column(name="unique_identifier", type="string", length=8, nullable=true)
     */
    private $uniqueIdentifier;
    /**
     * @var \DvsaEntities\Entity\TestSlotTransactionStatus
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\TestSlotTransactionStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var \DvsaEntities\Entity\Payment
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\Payment", fetch="EAGER")
     * @ORM\JoinColumn(name="payment_id", referencedColumnName="id")
     */
    private $payment;

    /**
     * @var \DvsaEntities\Entity\Organisation
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\Organisation", fetch="EAGER")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     */
    private $organisation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="created_by_username", type="string", length=100, nullable=false)
     */
    private $createdByUsername;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="completed_on", type="datetime", nullable=true)
     */
    private $completedOn;

    /**
     * @var TestSlotTransactionAmendment[]
     *
     * @ORM\OneToMany(targetEntity="TestSlotTransactionAmendment", mappedBy="testSlotTransaction")
     */
    private $transactionAmendments;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->created = new \DateTime('now');
        $this->transactionAmendments = [];
    }

    /**
     * Set slots.
     *
     * @param int $slots
     *
     * @return TestSlotTransaction
     */
    public function setSlots($slots)
    {
        $this->slots = $slots;

        return $this;
    }

    /**
     * Get slots.
     *
     * @return int
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Set status.
     *
     * @param \DvsaEntities\Entity\TestSlotTransactionStatus $status
     *
     * @return TestSlotTransaction
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return \DvsaEntities\Entity\TestSlotTransactionStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $state
     *
     * @return TestSlotTransaction
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set payment.
     *
     * @param \DvsaEntities\Entity\payment $payment
     *
     * @return TestSlotTransaction
     */
    public function setPayment(Payment $payment = null)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Get payment.
     *
     * @return \DvsaEntities\Entity\payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Set organisation.
     *
     * @param \DvsaEntities\Entity\Organisation $organisation
     *
     * @return TestSlotTransaction
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
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return TestSlotTransaction
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set createdByUsername.
     *
     * @param string $createdByUsername
     *
     * @return TestSlotTransaction
     */
    public function setCreatedByUsername($createdByUsername)
    {
        $this->createdByUsername = $createdByUsername;

        return $this;
    }

    /**
     * Get CreatedByUsername.
     *
     * @return string
     */
    public function getCreatedByUsername()
    {
        return $this->createdByUsername;
    }

    /**
     * Set completedOn.
     *
     * @param \DateTime $completedOn
     *
     * @return TestSlotTransaction
     */
    public function setCompletedOn($completedOn)
    {
        $this->completedOn = $completedOn;

        return $this;
    }

    /**
     * Get completedOn.
     *
     * @return \DateTime
     */
    public function getCompletedOn()
    {
        return $this->completedOn;
    }

    /**
     * @return string
     */
    public function getSalesReference()
    {
        return $this->salesReference;
    }

    /**
     * @param $salesReference
     *
     * @return $this
     */
    public function setSalesReference($salesReference)
    {
        $this->salesReference = $salesReference;
        if (is_string($salesReference)) {
            $this->setUniqueIdentifier(substr($salesReference, -8));
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getSlotsAfter()
    {
        return $this->slotsAfter;
    }

    /**
     * @param int $slotsAfter
     *
     * @return $this
     */
    public function setSlotsAfter($slotsAfter)
    {
        $this->slotsAfter = $slotsAfter;

        return $this;
    }

    /**
     * @return int
     */
    public function getRealSlots()
    {
        return $this->realSlots;
    }

    /**
     * @param int $realSlots
     *
     * @return $this
     */
    public function setRealSlots($realSlots)
    {
        $this->realSlots = $realSlots;

        return $this;
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

    /**
     * @return TestSlotTransactionAmendment[]
     */
    public function getTransactionAmendments()
    {
        return $this->transactionAmendments;
    }

    /**
     * @param $transactionAmendments
     */
    public function setTransactionAmendments($transactionAmendments)
    {
        $this->transactionAmendments = $transactionAmendments;
    }
}
