<?php
namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * MotTestReasonForCancel
 *
 * @ORM\Table(name="mot_test_reason_for_cancel_lookup")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\MotTestReasonForCancelRepository")
 */
class MotTestReasonForCancel
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="string", length=100, nullable=false)
     */
    private $reason;

    /**
     * @var string
     *
     * @ORM\Column(name="reason_cy", type="string", length=100, nullable=false)
     */
    private $reasonCy;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_abandoned", type="boolean", nullable=false)
     */
    private $abandoned;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_displayable", type="boolean", nullable=false)
     */
    private $isDisplayable;

    /**
     * @param bool $abandoned
     *
     * @return MotTestReasonForCancel
     */
    public function setAbandoned($abandoned)
    {
        $this->abandoned = $abandoned;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getAbandoned()
    {
        return $this->abandoned;
    }

    /**
     * @param string $reason
     *
     * @return MotTestReasonForCancel
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     *
     * @return MotTestReasonForCancel
     */
    public function setReasonCy($reason)
    {
        $this->reasonCy = $reason;

        return $this;
    }

    /**
     * @return string
     */
    public function getReasonCy()
    {
        return $this->reasonCy;
    }

    public function isDisplayable()
    {
        return $this->isDisplayable;
    }
}
