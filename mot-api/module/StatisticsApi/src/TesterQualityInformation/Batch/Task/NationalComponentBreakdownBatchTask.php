<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Task;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Service\NationalComponentStatisticsService;
use DvsaCommon\Date\DateRangeInterface;

class NationalComponentBreakdownBatchTask
{
    private $service;
    private $vehicleGroup;
    private $dateRange;
    private $reportYear;
    private $reportMonth;

    public function __construct($vehicleGroup, DateRangeInterface $monthRange, NationalComponentStatisticsService $service, $reportYear, $reportMonth)
    {
        $this->service = $service;
        $this->vehicleGroup = $vehicleGroup;
        $this->dateRange = $monthRange;
        $this->reportYear = $reportYear;
        $this->reportMonth = $reportMonth;
    }

    public function execute()
    {
        $this->service->get(
            $this->dateRange,
            $this->vehicleGroup,
            $this->reportYear,
            $this->reportMonth
        );
    }

    public function getName()
    {
        return sprintf(
            'National component breakdown batch task (Vehicle Group %s) - %s',
            $this->vehicleGroup,
            $this->dateRange->getNumberOfMonths()
        );
    }

    public function getMonthRange()
    {
        return $this->dateRange->getNumberOfMonths();
    }

    /**
     * @param NationalComponentBreakdownBatchTask[] $tasks
     *
     * @return NationalComponentBreakdownBatchTask[]
     */
    public static function sortTaskByMonthRange(array $tasks)
    {
        $taskComparator = function (NationalComponentBreakdownBatchTask $taskA, NationalComponentBreakdownBatchTask $taskB) {
            return $taskA->getMonthRange() <=> $taskB->getMonthRange();
        };

        usort($tasks, $taskComparator);

        return $tasks;
    }

}
