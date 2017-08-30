<?php
namespace DvsaCommon\ApiClient\Statistics\TesterPerformance;


use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterMultiSitePerformanceReportDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class TesterMultiSitePerformanceApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @param int $personId
     * @param int $monthRange
     * @return TesterMultiSitePerformanceReportDto
     */
    public function get(int $personId, int $monthRange):TesterMultiSitePerformanceReportDto
    {
        return $this->getSingle(
            TesterMultiSitePerformanceReportDto::class,
            sprintf('statistic/tester-performance/multi-site/%s/%s', $personId, $monthRange)
        );
    }
}
