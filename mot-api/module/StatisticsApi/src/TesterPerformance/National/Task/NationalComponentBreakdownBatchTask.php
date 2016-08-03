<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Task;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Service\NationalComponentStatisticsService;
use DvsaCommon\Date\Month;

class NationalComponentBreakdownBatchTask extends AbstractBatchTask
{
    private $service;
    private $vehicleGroup;

    function __construct($vehicleGroup, Month $month, NationalComponentStatisticsService $service)
    {
        parent::__construct($month);
        $this->service = $service;
        $this->vehicleGroup = $vehicleGroup;
    }

    public function execute()
    {
        $this->service->get(
            $this->getMonth()->getYear(),
            $this->getMonth()->getMonth(),
            $this->vehicleGroup
        );
    }

    public function getName()
    {
        return sprintf(
            "National component breakdown batch task (Vehicle Group %s)- %s/%s",
            $this->vehicleGroup,
            $this->getMonth()->getYear(),
            $this->getMonth()->getMonth()
        );
    }

}
