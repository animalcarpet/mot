<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Task;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;

class NationalTesterStatisticsBatchTask
{
    private $service;
    private $monthRange;

    public function __construct(int $monthRange, NationalStatisticsService $service)
    {
        $this->service = $service;
        $this->monthRange = $monthRange;
    }

    public function execute()
    {
        $this->service->get($this->monthRange);
    }

    public function getName()
    {
        return sprintf('National tester performance batch task - %s', $this->monthRange);
    }
}
