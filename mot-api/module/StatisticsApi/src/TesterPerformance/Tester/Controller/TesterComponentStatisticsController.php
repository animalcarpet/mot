<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\Service\TesterComponentStatisticsService;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;

class TesterComponentStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{

    private $service;

    function __construct(TesterComponentStatisticsService $componentStatisticsService)
    {
        $this->service = $componentStatisticsService;
    }

    public function getList()
    {
        if (!$this->isFeatureEnabled(FeatureToggle::TEST_QUALITY_INFORMATION)) {
            return ApiResponse::jsonOk();
        }

        $siteId = $this->params()->fromRoute('siteId');
        $testerId = $this->params()->fromRoute('testerId');
        $group = $this->params()->fromRoute('group');
        $year = (int)$this->params()->fromRoute("year");
        $month = (int)$this->params()->fromRoute("month");

        $componentStatisticsDto = $this->service->get($siteId, $testerId, $group, $year, $month);

        return $this->returnDto($componentStatisticsDto);
    }
}