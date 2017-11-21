<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service\BatchStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service\TesterPerformanceBatchStatisticsService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

class NationalBatchStatisticsForMonthController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $batchStatisticsService;
    private $testerPerformanceBatchStatisticsService;

    public function __construct(
        BatchStatisticsService $service,
        TesterPerformanceBatchStatisticsService $testerPerformanceBatchStatisticsService
    )
    {
        $this->batchStatisticsService = $service;
        $this->testerPerformanceBatchStatisticsService = $testerPerformanceBatchStatisticsService;
    }

    public function getList()
    {
        $year = (int) $this->params()->fromRoute('year');
        $month = (int) $this->params()->fromRoute('month');
        $day = (int) $this->params()->fromRoute('day');

        return $this->returnDto(array_merge(
            $this->batchStatisticsService->generateReportsForDate($year, $month, $day),
            $this->testerPerformanceBatchStatisticsService->generateReportsForDate($year, $month, $day)
        ));
    }
}
