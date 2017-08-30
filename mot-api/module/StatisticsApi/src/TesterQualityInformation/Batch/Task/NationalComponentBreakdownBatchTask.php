<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Task;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Service\NationalComponentStatisticsService;

class NationalComponentBreakdownBatchTask
{
    private $service;
    private $vehicleGroup;
    private $monthRange;

    public function __construct($vehicleGroup, int $monthRange, NationalComponentStatisticsService $service)
    {
        $this->service = $service;
        $this->vehicleGroup = $vehicleGroup;
        $this->monthRange = $monthRange;
    }

    public function execute()
    {
        $this->service->get(
            $this->monthRange,
            $this->vehicleGroup
        );
    }

    public function getName()
    {
        return sprintf(
            'National component breakdown batch task (Vehicle Group %s) - %s',
            $this->vehicleGroup,
            $this->monthRange
        );
    }

    public function getMonthRange()
    {
        return $this->monthRange;
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
