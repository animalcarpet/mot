<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Service\BatchStatisticsService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

class NationalBatchStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $service;

    function __construct(BatchStatisticsService $service)
    {
        $this->service = $service;
    }

    public function getList($group)
    {
        $dtos = $this->service->generateReports();

        return $this->returnDto($dtos);
    }
}
