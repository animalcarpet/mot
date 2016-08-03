<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Service\NationalComponentStatisticsService;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;

class NationalComponentStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $service;

    public function __construct(NationalComponentStatisticsService $service)
    {
        $this->service = $service;
    }

    public function get($group)
    {
        /**
         * do not throw exception when cron hits this endpoint
         */
        if (!$this->isFeatureEnabled(FeatureToggle::TEST_QUALITY_INFORMATION)) {
            return ApiResponse::jsonOk();
        }

        $year = (int)$this->params()->fromRoute("year");
        $month = (int)$this->params()->fromRoute("month");

        $dto = $this->service->get($year, $month, strtoupper($group));

        return $this->returnDto($dto);
    }
}
