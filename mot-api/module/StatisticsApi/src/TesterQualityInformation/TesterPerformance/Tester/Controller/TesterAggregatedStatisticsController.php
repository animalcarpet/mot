<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Service\TesterStatisticsService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

class TesterAggregatedStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $service;

    public function __construct(TesterStatisticsService $service)
    {
        $this->service = $service;
    }

    public function get($id)
    {
        $testerId = (int) $this->params()->fromRoute('id');
        $monthRange = (int) $this->params()->fromRoute('monthRange');

        return $this->returnDto($this->service->getForTester($testerId, $monthRange));
    }
}
