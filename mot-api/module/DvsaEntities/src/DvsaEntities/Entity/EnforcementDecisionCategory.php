<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(name="enforcement_decision_category_lookup", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class EnforcementDecisionCategory
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=50, nullable=true)
     */
    protected $category;

    /**
     * @var int
     *
     * @ORM\Column(name="display_order", type="smallint", nullable=false)
     */
    protected $position;

    /**
     * @param string $category
     *
     * @return EnforcementDecisionCategory
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param int $position
     *
     * @return EnforcementDecisionCategory
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
