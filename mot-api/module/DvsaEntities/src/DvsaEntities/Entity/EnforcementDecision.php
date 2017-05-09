<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="enforcement_decision_lookup", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class EnforcementDecision
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="decision", type="string", length=100, nullable=true)
     */
    protected $decision;

    /**
     * @var int
     *
     * @ORM\Column(name="display_order", type="smallint", nullable=false, unique=true)
     */
    protected $position;

    /**
     * @param string $decision
     *
     * @return EnforcementDecision
     */
    public function setDecision($decision)
    {
        $this->decision = $decision;

        return $this;
    }

    /**
     * @return string
     */
    public function getDecision()
    {
        return $this->decision;
    }

    /**
     * @param int $position
     *
     * @return EnforcementDecision
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}
