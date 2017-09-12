<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Service\MultipleTestersAtSiteComponentStatisticsService;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Service\TesterAtSiteComponentStatisticsService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

class MultipleTestersAtSiteComponentStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $service;
    private $multipleTestersService;

    public function __construct(TesterAtSiteComponentStatisticsService $componentStatisticsService,
                                MultipleTestersAtSiteComponentStatisticsService $multipleTestersComponentStatisticsService)
    {
        $this->service = $componentStatisticsService;
        $this->multipleTestersService = $multipleTestersComponentStatisticsService;
        $this->setIdentifierName('siteId');
    }

    public function get($siteId)
    {
        $group = $this->params()->fromRoute('group');
        $monthRange = (int) $this->params()->fromRoute('monthRange');

        $componentStatisticsDto = $this->multipleTestersService->get($siteId, $group, $monthRange);

        return $this->returnDto($componentStatisticsDto);
    }
}
