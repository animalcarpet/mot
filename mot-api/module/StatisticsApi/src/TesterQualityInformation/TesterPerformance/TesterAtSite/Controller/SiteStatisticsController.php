<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterAtSite\Service\TesterStatisticsService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

class SiteStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $siteStatisticsService;

    public function __construct(TesterStatisticsService $siteStatisticsService)
    {
        $this->siteStatisticsService = $siteStatisticsService;
    }

    public function get($siteId)
    {
        $year = (int) $this->params()->fromRoute('year');
        $month = (int) $this->params()->fromRoute('month');

        $dto = $this->siteStatisticsService->getForSite($siteId, $year, $month);

        return $this->returnDto($dto);
    }
}
