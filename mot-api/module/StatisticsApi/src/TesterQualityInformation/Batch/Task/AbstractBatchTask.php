<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\Batch\Task;

use DvsaCommon\Date\Month;

abstract class AbstractBatchTask
{
    private $month;

    public function __construct(Month $month)
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
