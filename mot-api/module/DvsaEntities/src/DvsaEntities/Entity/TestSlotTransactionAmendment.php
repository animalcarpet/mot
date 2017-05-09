<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * TestSlotTransactionAmendment.
 *
 * @ORM\Table(name="test_slot_transaction_amendment")
 * @ORM\Entity
 */
class TestSlotTransactionAmendment extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\Organisation
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Organisation")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     */
    private $organisation;

    /**
     * @var TestSlotTransaction
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\TestSlotTransaction", inversedBy="transactionAmendments")
     * @ORM\JoinColumn(name="test_slot_transaction_id", referencedColumnName="id")
     */
    private $testSlotTransaction;

    /**
     * @var \DvsaEntities\Entity\TestSlotTransactionAmendmentType
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\TestSlotTransactionAmendmentType")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     * })
     */
    private $type;

    /**
     * @var \DvsaEntities\Entity\TestSlotTransactionAmendmentReason
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\TestSlotTransactionAmendmentReason")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="reason_id", referencedColumnName="id")
     * })
     */
    private $reason;

    /**
     * @var int
     *
     * @ORM\Column(name="slots", type="integer", nullable=false)
     */
    private $slots;

    /**
     * @var string
     *
     * @ORM\Column(name="previous_receipt_reference", type="string", length=55, nullable=true)
     */
    private $previousReceiptReference;

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
     * @return TestSlotTransaction
     */
    public function getTestSlotTransaction()
    {
        return $this->testSlotTransaction;
    }

    /**
     * @param TestSlotTransaction $testSlotTransaction
     *
     * @return $this
     */
    public function setTestSlotTransaction($testSlotTransaction)
    {
        $this->testSlotTransaction = $testSlotTransaction;

        return $this;
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
    public function getPreviousReceiptReference()
    {
        return $this->previousReceiptReference;
    }

    /**
     * @param string $prevReceiptReference
     *
     * @return $this
     */
    public function setPreviousReceiptReference($prevReceiptReference)
    {
        $this->previousReceiptReference = $prevReceiptReference;

        return $this;
    }
    /**
     * @return TestSlotTransactionAmendmentType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param TestSlotTransactionAmendmentType $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return TestSlotTransactionAmendmentReason
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param TestSlotTransactionAmendmentReason $reason
     *
     * @return $this
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }
}
