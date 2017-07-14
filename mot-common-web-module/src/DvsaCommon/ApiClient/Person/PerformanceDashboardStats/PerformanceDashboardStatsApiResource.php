<?php

namespace DvsaCommon\ApiClient\Person\PerformanceDashboardStats;

use DvsaCommon\ApiClient\Person\PerformanceDashboardStats\Dto\PerformanceDashboardStatsDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class PerformanceDashboardStatsApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @param $personId
     * @return PerformanceDashboardStatsDto
     */
    public function getStats($personId)
    {
        return $this->getSingle(PerformanceDashboardStatsDto::class, 'person/' . $personId . '/stats');
    }
}
