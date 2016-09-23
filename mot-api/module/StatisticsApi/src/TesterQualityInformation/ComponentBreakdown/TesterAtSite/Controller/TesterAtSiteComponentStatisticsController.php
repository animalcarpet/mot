<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterAtSite\Service\TesterAtSiteComponentStatisticsService;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;

class TesterAtSiteComponentStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{

    private $service;

    function __construct(TesterAtSiteComponentStatisticsService $componentStatisticsService)
    {
        $this->service = $componentStatisticsService;
        $this->setIdentifierName("testerId");
    }

    public function get($testerId)
    {
        $siteId = $this->params()->fromRoute('siteId');
        $group = $this->params()->fromRoute('group');
        $year = (int)$this->params()->fromRoute("year");
        $month = (int)$this->params()->fromRoute("month");

        $componentStatisticsDto = $this->service->get($siteId, $testerId, $group, $year, $month);

        return $this->returnDto($componentStatisticsDto);
    }
}