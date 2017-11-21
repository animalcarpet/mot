<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Report\NationalComponentStatisticsReportGenerator;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Repository\NationalComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Storage\NationalComponentFailRateStorage;
use DvsaCommon\Date\DateRangeInterface;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class NationalComponentStatisticsService implements AutoWireableInterface
{
    private $repository;
    private $storage;
    private $lastMonthsDateRange;

    public function __construct(
        NationalComponentFailRateStorage $storage,
        NationalComponentStatisticsRepository $componentStatisticsRepository,
        LastMonthsDateRange $lastMonthsDateRange
    ) {
        $this->repository = $componentStatisticsRepository;
        $this->storage = $storage;
        $this->lastMonthsDateRange = $lastMonthsDateRange;
    }

    public function get(DateRangeInterface $dateRange, $group, int $reportYear, int $reportMonth)
    {
        $generator = new NationalComponentStatisticsReportGenerator(
            $this->storage,
            $this->repository,
            new TimeSpan(0, 1, 0, 0),
            $dateRange,
            $group,
            $reportYear,
            $reportMonth,
            new DateTimeHolder()
        );

        return $generator->get();
    }
}
