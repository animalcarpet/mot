<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Service\BatchStatisticsService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

class NationalBatchStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $service;

    public function __construct(BatchStatisticsService $service)
    {
        $this->service = $service;
    }

    public function getList()
    {
        $dtos = $this->service->generateReports();

        return $this->returnDto($dtos);
    }
}
