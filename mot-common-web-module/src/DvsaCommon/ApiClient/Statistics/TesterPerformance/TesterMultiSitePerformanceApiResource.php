<?php
namespace DvsaCommon\ApiClient\Statistics\TesterPerformance;


use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\TesterMultiSitePerformanceReportDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class TesterMultiSitePerformanceApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @param $personId
     * @param $month
     * @param $year
     * @return TesterMultiSitePerformanceReportDto
     */
    public function get($personId, $month, $year)
    {
        return $this->getSingle(
            TesterMultiSitePerformanceReportDto::class,
            sprintf('statistic/tester-performance/multi-site/%s/%s/%s', $personId, $year, $month)
        );
    }
}
