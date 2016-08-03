<?php

namespace DvsaCommon\Date;

use DvsaCommon\Utility\TypeCheck;

class Month
{
    private $month;
    private $year;

    public function __construct($year, $month)
    {
        TypeCheck::assertInteger($year);
        TypeCheck::assertInteger($month);

        $year = (int)$year;
        $month = (int)$month;

        if ($month <= 0 || $month > 12) {
            throw new \InvalidArgumentException('Month must be between values 1 and 12');
        }

        $this->year = $year;
        $this->month = $month;
    }

    public function getMonth()
    {
        return $this->month;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function equals(Month $month)
    {
        return $this->month == $month->getMonth()
        && $this->year == $month->getYear();
    }

    public function greaterThan(Month $other)
    {
        if ($this->year == $other->getYear()) {
            return $this->month > $other->getMonth();
        }

        return $this->year > $other->getYear();
    }

    public function getStartDate()
    {
        return new \DateTime(sprintf('%s-%s-1 00:00:00', $this->year, $this->month));
    }

    public function getStartDateAsString()
    {
        return $this->getStartDate()->format(DateUtils::FORMAT_ISO_WITH_TIME);
    }

    public function getEndDate()
    {
        $lastDay = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
        return new \DateTime(sprintf('%s-%s-%s 23:59:59', $this->year, $this->month, $lastDay));
    }

    public function getEndDateAsString()
    {
        return $this->getEndDate()->format(DateUtils::FORMAT_ISO_WITH_TIME);
    }

    public function previous()
    {
        $month = $this->month;
        $year = $this->year;

        $month -= 1;
        $year = $month == 0 ? $year - 1 : $year;
        $month = $month == 0 ? 12 : $month;

        return new Month($year, $month);
    }
}
