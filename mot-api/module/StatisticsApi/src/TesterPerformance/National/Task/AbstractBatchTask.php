<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\Task;

use DvsaCommon\Date\Month;

abstract class AbstractBatchTask
{
    private $month;

    function __construct(Month $month)
    {
        $this->month = $month;
    }

    public function getMonth()
    {
        return $this->month;
    }

    abstract public function getName();

    abstract public function execute();
}
