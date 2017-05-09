<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterMultiSite\Service\TesterMultiSiteStatisticsService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

class TesterMultiSiteStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $statisticsService;

    public function __construct(TesterMultiSiteStatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    public function get($testerId)
    {
        $testerId = (int) $testerId;

        $year = (int) $this->params()->fromRoute('year');
        $month = (int) $this->params()->fromRoute('month');

        $dto = $this->statisticsService->get($testerId, $year, $month);

        return $this->returnDto($dto);
    }
}
