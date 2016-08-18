<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\Task;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Task\AbstractBatchTask;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\TesterNational\Service\NationalStatisticsService;
use DvsaCommon\Date\Month;

class NationalTesterStatisticsBatchTask extends AbstractBatchTask
{
    private $service;

    function __construct(Month $month, NationalStatisticsService $service)
    {
        parent::__construct($month);
        $this->service = $service;
    }

    public function execute()
    {
        $this->service->get($this->getMonth()->getYear(), $this->getMonth()->getMonth());
    }

    public function getName()
    {
        return sprintf("National tester performance batch task - %s/%s", $this->getMonth()->getYear(), $this->getMonth()->getMonth());
    }
}
