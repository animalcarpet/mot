<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Service\NationalComponentStatisticsService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

class NationalComponentStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $service;

    public function __construct(NationalComponentStatisticsService $service)
    {
        $this->service = $service;
    }

    public function get($group)
    {
        $monthRange = (int) $this->params()->fromRoute('monthRange');

        $dto = $this->service->get($monthRange, strtoupper($group));

        return $this->returnDto($dto);
    }
}
