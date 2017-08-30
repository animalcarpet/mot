<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance;

use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class NationalPerformanceApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @param int $numberOfMonths
     * @return NationalPerformanceReportDto
     */
    public function getForMonths(int $numberOfMonths):NationalPerformanceReportDto
    {
        return $this->getSingle(
            NationalPerformanceReportDto::class,
            sprintf('statistic/tester-performance/national/%d', $numberOfMonths)
        );
    }
}
