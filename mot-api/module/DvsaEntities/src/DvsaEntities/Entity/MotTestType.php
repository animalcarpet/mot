<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * MotTestType.
 *
 * @ORM\Table(
 *  name="mot_test_type",
 *  options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class MotTestType extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="display_order", type="smallint", nullable=false)
     */
    protected $position;
    /**
     * @var bool
     *
     * @ORM\Column(name="is_demo", type="boolean", nullable=false)
     */
    protected $isDemo = 0;
    /**
     * @var bool
     *
     * @ORM\Column(name="is_slot_consuming", type="boolean", nullable=false)
     */
    protected $isSlotConsuming = 1;
    /**
     * @var bool
     *
     * @ORM\Column(name="is_reinspection", type="boolean", nullable=false)
     */
    protected $isReinspection = 0;
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=2, nullable=false)
     */
    private $code;
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=50, nullable=false)
     */
    private $description;

    /**
     * @param bool $isDemo
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setIsDemo($isDemo)
    {
        $this->isDemo = $isDemo;

        return $this;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function getIsDemo()
    {
        return $this->isDemo;
    }

    /**
     * @param bool $isReinspection
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setIsReinspection($isReinspection)
    {
        $this->isReinspection = $isReinspection;

        return $this;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function getIsReinspection()
    {
        return $this->isReinspection;
    }

    /**
     * Standard test done in a VTS by testers, not a demo test, not a VE re-inspection etc.
     *
     * @return bool
     */
    public function isStandardAtSiteTest()
    {
        return \DvsaCommon\Domain\MotTestType::isStandard($this->getCode());
    }

    /**
     * @param bool $isSlotConsuming
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setIsSlotConsuming($isSlotConsuming)
    {
        $this->isSlotConsuming = $isSlotConsuming;

        return $this;
    }

    /**
     * @return bool
     * @codeCoverageIgnore
     */
    public function getIsSlotConsuming()
    {
        return $this->isSlotConsuming;
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
     * Set description.
     *
     * @param string $description
     *
     * @return MotTestType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
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
     * @param string $code
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function isNonMotTest()
    {
        return \DvsaCommon\Domain\MotTestType::isNonMotTypes($this->getCode());
    }
}
