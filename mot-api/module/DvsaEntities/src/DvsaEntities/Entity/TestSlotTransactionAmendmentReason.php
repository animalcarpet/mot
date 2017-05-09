<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Reasons for amending a transaction.
 *
 * @ORM\Table(name="test_slot_transaction_amendment_reason", options={"collate"="utf8_general_ci", "charset"="utf8",
 *                                                           "engine"="InnoDB"})
 * @ORM\Entity
 */
class TestSlotTransactionAmendmentReason extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=5, unique=true)
     */
    private $code;
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=75)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="display_order", type="smallint", nullable=false)
     */
    private $displayOrder;

    /**
     * @var TestSlotTransactionAmendmentType
     *
     * @ORM\ManyToOne(targetEntity="TestSlotTransactionAmendmentType", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="amendment_type_id", referencedColumnName="id")
     * })
     */
    private $amendmentType;

    /**
     * @return TestSlotTransactionAmendmentType
     */
    public function getAmendmentType()
    {
        return $this->amendmentType;
    }

    /**
     * @param TestSlotTransactionAmendmentType $amendmentType
     *
     * @return $this
     */
    public function setAmendmentType($amendmentType)
    {
        $this->amendmentType = $amendmentType;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Set name.
     *
     * @param string $description
     *
     * @return \DvsaEntities\Entity\PaymentType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set display order.
     *
     * @param int $displayOrder
     *
     * @return \DvsaEntities\Entity\PaymentType
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    /**
     * Get display order.
     *
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }
}
