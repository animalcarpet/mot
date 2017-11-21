<?php

namespace DvsaCommon\Date;

use DvsaCommon\Utility\TypeCheck;

class Month
{
    private $day;
    private $month;
    private $year;

    public function __construct($year, $month, $day = null)
    {
        TypeCheck::assertInteger($year);
        TypeCheck::assertInteger($month);

        $year = (int)$year;
        $month = (int)$month;

        if ($month <= 0 || $month > 12) {
            throw new \InvalidArgumentException('Month must be between values 1 and 12');
        }

        if ($day === null) {
            $day = self::getLastDayOfMonth($month, $year);
        } else {
            TypeCheck::assertInteger($day);
            $day = (int) $day;
        }

        DateUtils::checkDate($year, $month, $day);

        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
    }

    public function getDay()
    {
        return $this->day;
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
        && $this->year == $month->getYear()
        && $this->day == $month->getDay();
    }

    public function getStartDate(): \DateTime
    {
        return new \DateTime(sprintf('%s-%s-1 00:00:00', $this->year, $this->month));
    }

    public function getFullMonthName()
    {
        return (new \DateTime(sprintf('%s-%s-1 00:00:00', $this->year, $this->month)))->format('F');
    }

    public function getStartDateAsString()
    {
        return $this->getStartDate()->format(DateUtils::FORMAT_ISO_WITH_TIME);
    }

    public function getEndDate(): \DateTime
    {
        return new \DateTime(sprintf('%s-%s-%s 23:59:59', $this->year, $this->month, $this->day));
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

    public function next()
    {
        $month = $this->month;
        $year = $this->year;

        $month += 1;
        $year = $month == 13 ? $year + 1 : $year;
        $month = $month == 13 ? 1 : $month;

        return new Month($year, $month);
    }

    private function getLastDayOfMonth(int $month, int $year): int
    {
        return (int) cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }
}
