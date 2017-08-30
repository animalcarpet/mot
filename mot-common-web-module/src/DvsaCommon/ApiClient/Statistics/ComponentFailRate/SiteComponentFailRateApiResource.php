<?php
namespace DvsaCommon\ApiClient\Statistics\ComponentFailRate;

use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentDto;
use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\SiteComponentStatisticsDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class SiteComponentFailRateApiResource extends AbstractApiResource implements AutoWireableInterface
{
    const PATH = 'statistic/component-fail-rate/site-average/%d/group/%s/%d';

    /**
     * @param $siteId
     * @param $group
     * @param $dateRange
     * @return SiteComponentStatisticsDto
     */
    public function get($siteId, $group, $dateRange)
    {
        return $this->getSingle(SiteComponentStatisticsDto::class, sprintf(self::PATH, $siteId, $group, $dateRange));
    }
}