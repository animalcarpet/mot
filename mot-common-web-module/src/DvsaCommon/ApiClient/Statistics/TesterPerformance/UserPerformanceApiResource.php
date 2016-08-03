<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance;

use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\ApiClient\Statistics\TesterPerformance\Dto\SitePerformanceDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class UserPerformanceApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @param int $siteId
     * @param int $userId
     * @param string $group
     * @return SitePerformanceDto
     */
    public function getForLastMonth($siteId, $userId, $group)
    {
        return $this->getSingle(
            ComponentBreakdownDto::class,
            sprintf('/statistic/component-fail-rate/site/%s/tester/%s/group/%s', $siteId, $userId, $group)
        );
    }
}
