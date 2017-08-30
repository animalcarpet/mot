<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

class NationalStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $nationalStatisticsService;

    public function __construct(NationalStatisticsService $nationalStatisticsService)
    {
        $this->nationalStatisticsService = $nationalStatisticsService;
    }

    public function getList()
    {
        $monthRange = (int) $this->params()->fromRoute('monthRange');

        $dto = $this->nationalStatisticsService->get($monthRange);

        return $this->returnDto($dto);
    }
}
