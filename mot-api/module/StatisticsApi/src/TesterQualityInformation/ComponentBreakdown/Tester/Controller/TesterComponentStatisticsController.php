<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Tester\Controller;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Tester\Service\TesterComponentStatisticsService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

class TesterComponentStatisticsController extends AbstractDvsaRestfulController implements AutoWireableInterface
{
    private $service;

    public function __construct(TesterComponentStatisticsService $service)
    {
        $this->service = $service;
        $this->setIdentifierName('testerId');
    }

    public function get($testerId)
    {
        $testerId = (int) $testerId;
        $group = $this->params()->fromRoute('group');
        $year = (int) $this->params()->fromRoute('year');
        $month = (int) $this->params()->fromRoute('month');

        $componentStatisticsDto = $this->service->get($testerId, $group, $year, $month);

        return $this->returnDto($componentStatisticsDto);
    }
}
