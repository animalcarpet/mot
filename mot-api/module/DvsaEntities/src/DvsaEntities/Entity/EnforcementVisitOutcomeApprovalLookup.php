<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * EnforcementVisitOutcomeApprovalLookup.
 *
 * @ORM\Table(name="enforcement_visit_outcome_approval_lookup", uniqueConstraints={@ORM\UniqueConstraint(name="position", columns={"position"})})
 * @ORM\Entity
 */
class EnforcementVisitOutcomeApprovalLookup
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=50, nullable=true)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="smallint", nullable=false)
     */
    private $position = '1';

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return EnforcementVisitOutcomeApprovalLookup
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set position.
     *
     * @param int $position
     *
     * @return EnforcementVisitOutcomeApprovalLookup
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}
