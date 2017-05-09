<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Payment.
 *
 * @ORM\Table(name="payment", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Entity
 */
class Payment extends Entity
{
    use CommonIdentityTrait;

    const TYPE_CARD = 'card';

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="receipt_reference", type="string", length=55)
     */
    private $receiptReference;
    /**
     * @var string
     *
     * @ORM\Column(name="unique_identifier", type="string", length=8, nullable=true)
     */
    private $uniqueIdentifier;
    /**
     * @var array
     *
     * @ORM\Column(name="payment_details", type="json_array", nullable=true)
     */
    private $details;

    /**
     * @var \DvsaEntities\Entity\PaymentStatus
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\PaymentStatus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var \DvsaEntities\Entity\PaymentType
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\PaymentType", fetch="EAGER")
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->created = new \DateTime('now');
    }

    /**
     * @return string
     */
    public function getReceiptReference()
    {
        return $this->receiptReference;
    }

    /**
     * @param string $receiptReference
     *
     * @return $this
     */
    public function setReceiptReference($receiptReference)
    {
        $this->receiptReference = $receiptReference;
        if (is_string($receiptReference)) {
            $this->setUniqueIdentifier(substr($receiptReference, -8));
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param array $details
     *
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Set amount.
     *
     * @param float $amount
     *
     * @return \DvsaEntities\Entity\Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return \DvsaEntities\Entity\Payment
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return \DvsaEntities\Entity\PaymentStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set type.
     *
     * @param \DvsaEntities\Entity\PaymentType $type
     *
     * @return \DvsaEntities\Entity\Payment
     */
    public function setType(\DvsaEntities\Entity\PaymentType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return \DvsaEntities\Entity\PaymentType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return \DvsaEntities\Entity\Payment
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
