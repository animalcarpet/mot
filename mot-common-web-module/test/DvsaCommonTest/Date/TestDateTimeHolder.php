<?php

namespace DvsaCommonTest\Date;

use DvsaCommon\Date\DateTimeHolder;

/**
 * Test implementation for DateTimeHolder
 *
 * Class TestDateTimeHolder
 *
 * @package DvsaCommonTest\Date
 */
class TestDateTimeHolder extends DateTimeHolder
{
    /**
     * @var \DateTime
     */
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
        $this->currentDateTime = clone $currentDateTime;
    }

    public function getCurrent($withMilliseconds = false)
    {
        return clone $this->currentDateTime;
    }

    public function getCurrentDate()
    {
        return clone $this->currentDateTime;
    }
}
