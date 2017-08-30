<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance;

use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\NationalPerformanceReportDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class SitePerformanceApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @param int $siteId
     * @param int $numberOfLastMonths
     * @return SitePerformanceDto
     */
    public function getForMonthRange(int $siteId, int $numberOfLastMonths)
    {
        return $this->getSingle(
            SitePerformanceDto::class,
            sprintf('statistic/tester-performance/site/%d/%d', $siteId, $numberOfLastMonths)
        );
    }
}
