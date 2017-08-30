<?php

namespace DvsaCommon\Date;

use DateTime;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class LastMonthsDateRange implements AutoWireableInterface
{
    const ONE_MONTH = 1;
    const THREE_MONTHS = 3;

    private $dateTimeHolder;

    /** @var  DateTime */
    private $startDate;

    /** @var  DateTime */
    private $endDate;
    private $numberOfMonths;

    public function __construct(DateTimeHolderInterface $dateTimeHolder)
    {
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function __toString()
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        $dateTemplate = '%s to %s';

        if ($this->numberOfMonths == 1) {
            return $startDate->format(DateTimeDisplayFormat::FORMAT_FULL_MONTH_WITH_YEAR);
        } else {
            if ($this->areDatesInTheSameYear($startDate, $endDate)) {
                return sprintf($dateTemplate,
                    $startDate->format(DateTimeDisplayFormat::FORMAT_FULL_MONTH),
                    $endDate->format(DateTimeDisplayFormat::FORMAT_FULL_MONTH_WITH_YEAR)
                );
            } else {
                return sprintf($dateTemplate,
                    $startDate->format(DateTimeDisplayFormat::FORMAT_FULL_MONTH_WITH_YEAR),
                    $endDate->format(DateTimeDisplayFormat::FORMAT_FULL_MONTH_WITH_YEAR)
                );
            }
        }
    }

    public function setNumberOfMonths(int $numberOfMonths):LastMonthsDateRange
    {
        $this->numberOfMonths = $numberOfMonths;
        $this->calculateDates($numberOfMonths, $this->dateTimeHolder->getCurrent());

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getStartDate():DateTime
    {
        return $this->startDate;
    }

    /**
     * @return DateTime
     */
    public function getEndDate():DateTime
    {
        return $this->endDate;
    }

    /**
     * @return int
     */
    public function getNumberOfMonths():int
    {
        return $this->numberOfMonths;
    }

    /**
     * @param int $numberOfMonths
     * @param DateTime $currentDate
     */
    private function calculateDates(int $numberOfMonths, DateTime $currentDate)
    {
        $currentMonth = new Month($currentDate->format('Y'), $currentDate->format('n'));
        $endDate = $currentMonth->previous();
        $startDate = $currentMonth;


        for ($i = 0; $i < $numberOfMonths; $i++) {
            $startDate = $startDate->previous();
        }

        $this->startDate = $startDate->getStartDate();
        $this->endDate = $endDate->getEndDate();
    }

    /**
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @return bool
     */
    private function areDatesInTheSameYear(DateTime $startDate, DateTime $endDate):bool
    {
        return $startDate->format('Y') == $endDate->format('Y');
    }

    /**
     * @return DateTimeHolderInterface
     */
    public function getDateTimeHolder():DateTimeHolderInterface
    {
        return $this->dateTimeHolder;
    }
}