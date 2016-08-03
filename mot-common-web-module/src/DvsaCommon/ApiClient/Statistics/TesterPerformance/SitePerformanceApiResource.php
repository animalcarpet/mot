<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance;

use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class SitePerformanceApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @param $siteId
     * @param $month
     * @param $year
     * @return SitePerformanceDto
     */
    public function getForDate($siteId, $month, $year)
    {
        return $this->getSingle(SitePerformanceDto::class, sprintf('statistic/tester-performance/site/%s/%s/%s', $siteId, $year, $month));
    }
}
