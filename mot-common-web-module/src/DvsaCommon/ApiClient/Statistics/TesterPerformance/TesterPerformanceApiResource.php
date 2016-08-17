<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance;

use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class TesterPerformanceApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @param $personId
     * @param $month
     * @param $year
     * @return TesterPerformanceDto
     */
    public function get($personId, $month, $year)
    {
        return $this->getSingle(TesterPerformanceDto::class, sprintf('statistic/tester-performance/tester/%s/%s/%s', $personId, $year, $month));
    }
}
