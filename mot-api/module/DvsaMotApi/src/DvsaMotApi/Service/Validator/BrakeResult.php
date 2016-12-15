<?php

namespace DvsaMotApi\Service\Validator;

/**
 * Class BrakeResult.
 */
class BrakeResult
{
    /**
     * @var string
     */
    private $control;

    /**
     * @var string
     */
    private $location;

    /**
     * @var int
     */
    private $effort;

    /**
     * @var bool
     */
    private $lock;

    /**
     * @return string
     */
    public function getControl()
    {
        return $this->control;
    }

    /**
     * @param string $control
     *
     * @return $this
     */
    public function setControl($control)
    {
        $this->control = $control;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     *
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return int
     */
    public function getEffort()
    {
        return $this->effort;
    }

    /**
     * @param int $effort
     *
     * @return $this
     */
    public function setEffort($effort)
    {
        $this->effort = $effort;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked()
    {
        return $this->lock;
    }

    /**
     * @param bool $lock
     *
     * @return $this
     */
    public function setLocked($lock)
    {
        $this->lock = $lock;

        return $this;
    }
}
