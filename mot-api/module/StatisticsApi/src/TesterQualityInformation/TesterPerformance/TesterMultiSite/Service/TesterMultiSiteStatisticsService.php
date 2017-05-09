<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Mapper\TesterStatisticsMapper;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\Repository\TesterMultiSiteStatisticsRepository;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TesterMultiSiteStatisticsService implements AutoWireableInterface
{
    private $repository;
    private $mapper;

    public function __construct(
        TesterMultiSiteStatisticsRepository $repository
    ) {
        $this->repository = $repository;
        $this->mapper = new TesterStatisticsMapper();
    }

    public function get($testerId, $year, $month)
    {
        $results = $this->repository->get($testerId, $year, $month);
        $dto = $this->mapper->buildTesterMultiSitePerformanceReportDto($results);

        return $dto;
    }
}
