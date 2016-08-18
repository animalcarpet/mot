<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Tester\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Tester\Service\TesterComponentStatisticsService;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;

class TesterComponentStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{

    private $service;

    function __construct(TesterComponentStatisticsService $service)
    {
        $this->service = $service;
        $this->setIdentifierName("testerId");
    }

    public function get($testerId)
    {
        if (!$this->isFeatureEnabled(FeatureToggle::TEST_QUALITY_INFORMATION)) {
            return ApiResponse::jsonOk();
        }

        $testerId = (int) $testerId;
        $group = $this->params()->fromRoute('group');
        $year = (int)$this->params()->fromRoute("year");
        $month = (int)$this->params()->fromRoute("month");

        $componentStatisticsDto = $this->service->get($testerId, $group, $year, $month);

        return $this->returnDto($componentStatisticsDto);
    }
}