<?php
namespace DvsaCommon\ApiClient\Statistics\ComponentFailRate;

use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class ComponentFailRateApiResource extends AbstractApiResource implements AutoWireableInterface
{
    const PATH_SITE_TESTER_GROUP = 'statistic/component-fail-rate/site/%d/tester/%d/group/%s/%s/%s';

    /**
     * @param $siteId
     * @param $testerId
     * @param $group
     * @param $month
     * @param $year
     * @return ComponentBreakdownDto
     */
    public function getForDate($siteId, $testerId, $group, $month, $year)
    {
        return $this->getSingle(ComponentBreakdownDto::class, sprintf(self::PATH_SITE_TESTER_GROUP, $siteId, $testerId, $group, $year, $month));
    }
}