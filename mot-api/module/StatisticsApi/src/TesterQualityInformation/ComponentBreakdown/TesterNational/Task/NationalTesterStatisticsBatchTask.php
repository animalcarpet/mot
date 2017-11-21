<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Task;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;
use DvsaCommon\Date\DateRangeInterface;

class NationalTesterStatisticsBatchTask
{
    private $service;
    private $dateRange;
    private $reportMonth;
    private $reportYear;

    public function __construct(
        NationalStatisticsService $service,
        int $reportYear,
        int $reportMonth,
        DateRangeInterface $dateRange
    )
    {
        $this->service = $service;
        $this->dateRange = $dateRange;
        $this->reportMonth = $reportMonth;
        $this->reportYear = $reportYear;
    }

    public function execute()
    {
        $this->service->get($this->dateRange, $this->reportYear, $this->reportMonth);
    }

    public function getName()
    {
        return sprintf('National tester performance batch task - %s', $this->dateRange->getNumberOfMonths());
    }
}
