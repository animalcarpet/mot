<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * MotTestReasonForRefusal.
 *
 * @ORM\Table(name="mot_test_reason_for_refusal_lookup")
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class ReasonForRefusal
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
     * @param string $reason
     *
     * @return ReasonForRefusal
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
     * @return ReasonForRefusal
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
}
