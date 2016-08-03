<?php

namespace DvsaCommon\Date;

class TimeSpan
{
    private $seconds;
    private $minutes;
    private $hours;
    private $days;

    /**
     * @param int $days
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     */
    public function __construct($days, $hours, $minutes, $seconds)
    {
        $totalSeconds = $seconds + $minutes * 60 + $hours * 3600 + $days * 86400;
        $isNegative = $totalSeconds < 0;

        $totalSeconds = abs($totalSeconds);
        $this->seconds = $totalSeconds % 60;

        $totalMinutes = floor($totalSeconds / 60);
        $this->minutes = $totalMinutes % 60;

        $totalHours = floor($totalMinutes / 60);
        $this->hours = $totalHours % 24;

        $totalDays = (int)floor($totalHours / 24);
        $this->days = $totalDays;

        if ($isNegative) {
            $this->seconds = -$this->seconds;
            $this->minutes = -$this->minutes;
            $this->hours = -$this->hours;
            $this->days = -$this->days;
        }
    }

    public function getSeconds()
    {
        return $this->seconds;
    }

    public function getTotalSeconds()
    {
        return $this->seconds + $this->minutes * 60 + $this->hours * 3600 + $this->days * 86400;
    }

    public function getMinutes()
    {
        return $this->minutes;
    }

    public function getTotalMinutes()
    {
        return $this->minutes + $this->hours * 60 + $this->days * 1440;
    }

    public function getHours()
    {
        return $this->hours;
    }

    public function getTotalHours()
    {
        return $this->hours + $this->days * 24;
    }

    public function getDays()
    {
        return $this->days;
    }

    public function getTotalDays()
    {
        return $this->days;
    }

    public function isPositive()
    {
        return !$this->isNegative();
    }

    public function isNegative()
    {
        return $this->getTotalSeconds() < 0;
    }

    public function equals(TimeSpan $other)
    {
        return $this->getTotalSeconds() == $other->getTotalSeconds();
    }

    public function negate(TimeSpan $timeSpan)
    {
        return new TimeSpan(0, 0, 0, -$timeSpan->getTotalSeconds());
    }

    public function addDateTime(\DateTime $dateTime)
    {
        $result = clone $dateTime;
        $interval = new \DateInterval('PT' . abs($this->getTotalSeconds()) . 'S');
        if ($this->isNegative()) {
            $result->sub($interval);
        } else {
            $result->add($interval);
        }

        return $result;
    }

    public static function testSubtractDates(\DateTime $date1, \DateTime $date2)
    {
        $seconds = $date1->getTimestamp() - $date2->getTimestamp();

        return new TimeSpan(0, 0, 0, $seconds);
    }
}
