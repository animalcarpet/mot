<?php

namespace Dashboard\ViewModel;

use Core\Formatting\FailedTestsPercentageFormatter;
use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto\DayPerformanceDashboardStatsDto;
use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto\MonthPerformanceDashboardStatsDto;
use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto\PerformanceDashboardStatsDto;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Date\DateUtils;

class UserStatsViewModel
{
    const FIRST_DAY_OF_PREVIOUS_MONTH = "first day of previous month";
    const DATE_YEAR_FORMAT = "Y";
    const DATE_MONTH_FORMAT = "m";
    const FAILED_RATE_DECIMALS = 0;

    private $monthStats;
    private $dayStats;

    /**
     * UserStatsViewModel constructor.
     *
     * @param PerformanceDashboardStatsDto $statsDto
     */
    public function __construct(PerformanceDashboardStatsDto $statsDto) {
        $this->monthStats = $statsDto->getMonthStats();
        $this->dayStats = $statsDto->getDayStats();
    }

    /**
     * @return DayPerformanceDashboardStatsDto
     */
    public function getDayStats() {
        return $this->dayStats;
    }

    /**
     * @return MonthPerformanceDashboardStatsDto
     */
    public function getMonthStats() {
        return $this->monthStats;
    }

    public function getMonthlyAverageTimeAsString() {
        return (string)$this->getMonthlyAverageTime()->getTotalMinutes();
    }

    public function getAndConvertPercentFailed() {
        $percentageFormatter = new FailedTestsPercentageFormatter();

        return $percentageFormatter->format($this->getMonthStats()->getFailRate());
    }

    private function getMonthlyAverageTime() {
        return $this->getMonthStats()->getAverageTime();
    }

    public function getCurrentDateAsDayMonth() {
        return DateUtils::toUserTz(new \DateTime())->format(DateTimeDisplayFormat::FORMAT_DAY_MONTH);
    }

    public function getFirstOfThisMonthAsDayMonth() {
        return DateUtils::firstOfThisMonth()->format(DateTimeDisplayFormat::FORMAT_DAY_MONTH);
    }

    public function getPreviousMonth() {
        return $this->getFirstOfPreviousMonth()->format(self::DATE_MONTH_FORMAT);
    }

    public function getPreviousYear() {
        return $this->getFirstOfPreviousMonth()->format(self::DATE_YEAR_FORMAT);
    }

    private function getFirstOfPreviousMonth() {
        return new \DateTime(self::FIRST_DAY_OF_PREVIOUS_MONTH);
    }
}