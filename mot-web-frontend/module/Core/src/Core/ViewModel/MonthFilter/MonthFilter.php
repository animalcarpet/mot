<?php

namespace Core\ViewModel\MonthFilter;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Month;
use Zend\Mvc\Controller\Plugin\Url;

abstract class MonthFilter
{
    /** @var Month $startMonth */
    private $startMonth;

    /** @var int $numberOfMonthsBack */
    private $numberOfMonthsBack;

    /** @var Month $viewedMonth */
    private $viewedMonth;

    /**
     * @param Url $url
     * @param $month
     * @param $year
     *
     * @return mixed
     */
    abstract public function getUrlForMonth(Url $url, $month, $year);

    public function setStartMonth($startMonth)
    {
        $this->startMonth = $startMonth;

        return $this;
    }

    public function setNumberOfMonthsBack($numberOfMonthsBack)
    {
        $this->numberOfMonthsBack = $numberOfMonthsBack;

        return $this;
    }

    public function setViewedMonth($viewedMonth)
    {
        $this->viewedMonth = $viewedMonth;

        return $this;
    }

    public function getMonthsNames()
    {
        $startDate = $this->startMonth->getStartDate();
        $list[$startDate->format('Y')][$startDate->format('m')] = [$startDate->format('F')];

        for ($i = 1; $i < $this->numberOfMonthsBack; ++$i) {
            $date = DateUtils::subtractCalendarMonths($startDate, $i);
            $list[$date->format('Y')][$date->format('m')] = [$date->format('F')];
        }

        return $list;
    }

    public function getViewedMonthName()
    {
        return $this->viewedMonth->getFullMonthName();
    }

    abstract public function getUrl();
}
