<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance;

use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class NationalPerformanceApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @param $month
     * @param $year
     * @return NationalPerformanceReportDto
     */
    public function getForDate($month, $year)
    {
        return $this->getSingle(NationalPerformanceReportDto::class, sprintf('statistic/tester-performance/national/%s/%s', $year, $month));
    }
}
