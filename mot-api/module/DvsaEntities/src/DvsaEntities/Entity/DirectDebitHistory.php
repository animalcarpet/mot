<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * DirectDebitHistory.
 *
 * @ORM\Table(name="direct_debit_history")
 * @ORM\Entity
 */
class DirectDebitHistory
{
    use CommonIdentityTrait;

    /**
     * @var \DvsaEntities\Entity\DirectDebit
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\DirectDebit")
     * @ORM\JoinColumn(name="direct_debit_id", referencedColumnName="id")
     */
    private $directDebit;

    /**
     * @var \DvsaEntities\Entity\TestSlotTransaction
     *
     * @ORM\OneToOne(targetEntity="DvsaEntities\Entity\TestSlotTransaction")
     * @ORM\JoinColumn(name="transaction_id", referencedColumnName="id")
     */
    private $transaction;

    /**
     * @var \DvsaEntities\Entity\DirectDebitHistoryStatus
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\DirectDebitHistoryStatus", cascade="persist")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * })
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="increment_date", type="datetime", nullable=false)
     */
    private $incrementDate;

    public function __construct()
    {
        $this->incrementDate = new \DateTime();
    }

    /**
     * @param \DvsaEntities\Entity\DirectDebit $directDebit
     *
     * @return $this
     */
    public function setDirectDebit($directDebit)
    {
        $this->directDebit = $directDebit;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\DirectDebit
     */
    public function getDirectDebit()
    {
        return $this->directDebit;
    }

    /**
     * @param \DateTime $incrementDate
     *
     * @return $this
     */
    public function setIncrementDate($incrementDate)
    {
        $this->incrementDate = $incrementDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getIncrementDate()
    {
        return $this->incrementDate;
    }

    /**
     * @param \DvsaEntities\Entity\DirectDebitHistoryStatus $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\DirectDebitHistoryStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param \DvsaEntities\Entity\TestSlotTransaction $transaction
     *
     * @return $this
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\TestSlotTransaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
}
