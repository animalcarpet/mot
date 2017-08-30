<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance;

use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterPerformanceDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class TesterPerformanceApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @param $personId
     * @param int $monthRange
     * @return TesterPerformanceDto
     */
    public function get(int $personId, int $monthRange):TesterPerformanceDto
    {
        return $this->getSingle(TesterPerformanceDto::class, sprintf('statistic/tester-performance/tester/%s/%s', $personId, $monthRange));
    }
}
