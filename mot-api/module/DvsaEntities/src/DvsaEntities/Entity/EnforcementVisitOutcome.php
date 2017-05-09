<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * @ORM\Entity(readOnly=true)
 *
 * @ORM\Table(name="enforcement_visit_outcome_lookup",
 * options={
 *  "collate"="utf8_general_ci",
 *  "charset"="utf8",
 *  "engine"="InnoDB"
 * })
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class EnforcementVisitOutcome
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=50, nullable=true)
     */
    protected $description;

    /**
     * @var int
     *
     * @ORM\Column(name="display_order", type="smallint", nullable=false)
     */
    protected $position;

    /**
     * @param string $description description
     *
     * @return EnforcementVisitOutcome
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $position position
     *
     * @return EnforcementVisitOutcome
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
