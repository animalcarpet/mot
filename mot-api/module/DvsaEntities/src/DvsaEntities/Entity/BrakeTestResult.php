<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Parent class for brake test results.
 */
class BrakeTestResult extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var bool
     *
     * @ORM\Column(name="general_pass", type="boolean", nullable=false)
     */
    protected $generalPass;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_latest", type="boolean", nullable=false)
     */
    protected $isLatest = true;

    /**
     * Set generalPass.
     *
     * @param bool $generalPass
     *
     * @return BrakeTestResult
     */
    public function setGeneralPass($generalPass)
    {
        $this->generalPass = $generalPass;

        return $this;
    }

    /**
     * Get generalPass.
     *
     * @return bool
     */
    public function getGeneralPass()
    {
        return $this->generalPass;
    }

    /**
     * @param bool $isLatest
     *
     * @return BrakeTestResult
     */
    public function setIsLatest($isLatest)
    {
        $this->isLatest = $isLatest;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsLatest()
    {
        return $this->isLatest;
    }
}
