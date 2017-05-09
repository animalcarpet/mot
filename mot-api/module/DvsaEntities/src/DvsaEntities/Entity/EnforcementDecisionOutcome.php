<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="enforcement_decision_outcome_lookup", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class EnforcementDecisionOutcome
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="outcome", type="string", length=50, nullable=true)
     */
    protected $outcome;

    /**
     * @var int
     *
     * @ORM\Column(name="display_order", type="smallint", nullable=false)
     */
    protected $position;

    /**
     * @param string $outcome
     *
     * @return EnforcementDecisionOutcome
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;

        return $this;
    }

    /**
     * @return string
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * @param int $position
     *
     * @return EnforcementDecisionOutcome
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
