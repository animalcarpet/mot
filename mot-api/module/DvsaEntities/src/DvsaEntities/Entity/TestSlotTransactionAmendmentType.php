<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Types of amendments that can be made on a transaction.
 *
 * @ORM\Table(name="test_slot_transaction_amendment_type", options={"collate"="utf8_general_ci", "charset"="utf8",
 *                                                         "engine"="InnoDB"})
 * @ORM\Entity
 */
class TestSlotTransactionAmendmentType extends Entity
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
     * @ORM\Column(name="title", type="string", length=75, nullable=false)
     */
    private $title;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private $isActive = true;

    /**
     * @var int
     *
     * @ORM\Column(name="display_order", type="smallint", nullable=false)
     */
    private $displayOrder;

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
     * @param string $name
     *
     * @return \DvsaEntities\Entity\PaymentType
     */
    public function setTitle($name)
    {
        $this->title = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return \DvsaEntities\Entity\PaymentType
     */
    public function setIsActive($active)
    {
        $this->isActive = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
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
