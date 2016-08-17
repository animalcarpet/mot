<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Site\Service\TesterStatisticsService;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;

class TesterAggregatedStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $service;

    public function __construct(TesterStatisticsService $service)
    {
        $this->service = $service;
    }

    public function get()
    {
        if (!$this->isFeatureEnabled(FeatureToggle::TEST_QUALITY_INFORMATION)) {
            return ApiResponse::jsonOk();
        }

        $testerId = (int)$this->params()->fromRoute('id');
        $year = (int)$this->params()->fromRoute("year");
        $month = (int)$this->params()->fromRoute("month");

        return $this->returnDto($this->service->getForTester($testerId, $year, $month));
    }
}
