<?php

namespace DvsaCommonTest\Date;

use DvsaCommon\Date\DateTimeHolder;

/**
 * Test implementation for DateTimeHolder.
 * This class is deprecated. It's left to enable MotTestStatusChangeServiceTest working.
 * The test was poorly designed and sadly no one can figure out what's going on inside.
 *
 * The problem with this implementation is that it doesn't clone the date and always worked on same DateTime object.
 * This allows to manipulate the object from outside of the DateTimeHolder.
 *
 * Class InvalidTestDateTimeHolder
 *
 * @package DvsaCommonTest\Date
 * @deprecated Please use TestDateTimeHolder
 */
class InvalidTestDateTimeHolder extends DateTimeHolder
{

    private $currentDateTime;

    /**
     * @param \DateTime $currentDateTime
     */
    public function __construct($currentDateTime)
    {
        $this->setCurrent($currentDateTime);
    }

    /**
     * Sets the current date in the holder
     *
     * @param \DateTime $currentDateTime
     */
    public function setCurrent(\DateTime $currentDateTime)
    {
        $this->currentDateTime = $currentDateTime;
    }

    public function getCurrent($withMilliseconds = false)
    {
        return $this->currentDateTime;
    }

    public function getCurrentDate()
    {
        return $this->currentDateTime;
    }
}
