<?php

namespace DvsaClient\ViewModel;

use DvsaCommon\Date\DateUtils;

class DateTimeViewModel
{
    /**
     * @var string
     */
    private $day = '1';
    /**
     * @var string
     */
    private $month = '1';
    /**
     * @var string
     */
    private $year;

    private $hour;
    private $minute;
    private $second;

    public function __construct($year = null, $month = null, $day = null, $hour = '0', $minute = '0', $second = '0')
    {
        $this->setYear($year);
        $this->setMonth($year && $month === null ? $this->month : $month);
        $this->setDay($year && $day === null ? $this->day : $day);

        $this->setHour($hour);
        $this->setMinute($minute);
        $this->setSecond($second);
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDate($date)
    {
        if ($date instanceof \DateTime) {
            $this->setYear($date->format('Y'));
            $this->setMonth($date->format('n'));
            $this->setDay($date->format('j'));
            $this->setHour($date->format('G'));
            $this->setMinute($date->format('i'));
            $this->setSecond($date->format('s'));
        }

        return $this;
    }

    /**
     * Returns correct date or null.
     *
     * @return \DateTime|null
     */
    public function getDate()
    {
        //  all check already made inside DateUtils library, so no reason to duplicate.
        //  this function return null in case of invalid date, so exception should be suppressed
        try {
            return DateUtils::toDateTimeFromParts(
                $this->getYear(),
                $this->getMonth(),
                $this->getDay(),
                $this->getHour(),
                $this->getMinute(),
                $this->getSecond()
            );
        } catch (\Exception $e) {
            //  if incorrect date just return null
        }

        return null;
    }

    /**
     * @return string
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param string $day
     *
     * @return DateTimeViewModel
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * @return string
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param string $month
     *
     * @return DateTimeViewModel
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * @return string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param string $year
     *
     * @return DateTimeViewModel
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * @return string
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * @return $this
     */
    public function setHour($hour)
    {
        $this->hour = $hour;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinute()
    {
        return $this->minute;
    }

    /**
     * @param string $minute
     *
     * @return $this
     */
    public function setMinute($minute)
    {
        $this->minute = $minute;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecond()
    {
        return $this->second;
    }

    /**
     * @return $this
     */
    public function setSecond($second)
    {
        $this->second = $second;

        return $this;
    }
}
