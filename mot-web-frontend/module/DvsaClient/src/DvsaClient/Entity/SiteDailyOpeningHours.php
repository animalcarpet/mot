<?php

namespace DvsaClient\Entity;

use DvsaCommon\Date\Time;

/**
 * Class SiteDailyOpeningHours.
 */
class SiteDailyOpeningHours
{
    public static $DAY_NAMES
        = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];

    private $weekday;
    /** @var Time */
    private $openTime;
    /** @var Time */
    private $closeTime;

    public function setWeekday($weekday)
    {
        $this->weekday = $weekday;

        return $this;
    }

    public function getDayName()
    {
        return self::$DAY_NAMES[$this->weekday];
    }

    public function setOpenTime(Time $openTime = null)
    {
        $this->openTime = $openTime;

        return $this;
    }

    public function setCloseTime(Time $closeTime = null)
    {
        $this->closeTime = $closeTime;

        return $this;
    }

    /**
     * @return \DvsaCommon\Date\Time
     */
    public function getOpenTime()
    {
        return $this->openTime;
    }

    public function getCloseTime()
    {
        return $this->closeTime;
    }

    public function isClosed()
    {
        return $this->openTime === null && $this->closeTime === null;
    }
}
