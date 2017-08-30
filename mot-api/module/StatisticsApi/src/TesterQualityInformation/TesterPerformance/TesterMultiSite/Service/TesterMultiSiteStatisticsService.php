<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Mapper\TesterStatisticsMapper;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\Repository\TesterMultiSiteStatisticsRepository;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\LastMonthsDateRange;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TesterMultiSiteStatisticsService implements AutoWireableInterface
{
    private $repository;
    private $mapper;
    private $dateTimeHolderInterface;

    public function __construct(
        TesterMultiSiteStatisticsRepository $repository,
        DateTimeHolderInterface $dateTimeHolderInterface
    ) {
        $this->repository = $repository;
        $this->mapper = new TesterStatisticsMapper();
        $this->dateTimeHolderInterface = $dateTimeHolderInterface;
    }

    public function get(int $testerId, int $monthRange)
    {
        $lastMonthsDateRange = new LastMonthsDateRange($this->dateTimeHolderInterface);
        $lastMonthsDateRange->setNumberOfMonths($monthRange);

        $results = $this->repository->get($testerId, $lastMonthsDateRange);
        $dto = $this->mapper->buildTesterMultiSitePerformanceReportDto($results);

        return $dto;
    }
}
