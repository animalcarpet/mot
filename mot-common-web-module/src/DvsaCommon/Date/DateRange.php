<?php

namespace DvsaCommon\Date;


use DateTime;

class DateRange implements DateRangeInterface
{
    private $startDate;
    private $endDate;

    function __construct(DateTime $startDate, DateTime $endDate)
    {
        $this->validate($startDate, $endDate);
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }
    public function getNumberOfMonths():int
    {
        return DateUtils::getTimeDifferenceInMonths($this->startDate, $this->endDate) + 1;
    }

    private function validate(DateTime $startDate, DateTime $endDate)
    {
        if ($startDate > $endDate) {
            throw new \InvalidArgumentException("Ending date is lower than the starting date");
        }
    }
}