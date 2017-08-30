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
        $monthRange = (int) $this->params()->fromRoute('monthRange');

        $dto = $this->siteStatisticsService->getForSite($siteId, $monthRange);

        return $this->returnDto($dto);
    }
}
