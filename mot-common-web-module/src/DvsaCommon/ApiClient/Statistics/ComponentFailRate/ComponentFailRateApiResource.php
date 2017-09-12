<?php
namespace DvsaCommon\ApiClient\Statistics\ComponentFailRate;

use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\ComponentBreakdownDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class ComponentFailRateApiResource extends AbstractApiResource implements AutoWireableInterface
{
    const PATH_SITE_TESTER_GROUP = 'statistic/component-fail-rate/site/%d/tester/%d/group/%s/monthRange/%d';
    const PATH_SITE_ALL_TESTERS_GROUP = 'statistic/component-fail-rate/site/%d/group/%s/monthRange/%d';

    /**
     * @param $siteId
     * @param $testerId
     * @param $group
     * @param $monthRange
     * @return ComponentBreakdownDto
     */
    public function getForTesterAtSite($siteId, $testerId, $group, $monthRange)
    {
        return $this->getSingle(ComponentBreakdownDto::class, sprintf(self::PATH_SITE_TESTER_GROUP, $siteId, $testerId, $group, $monthRange));
    }

    /**
     * @param int $siteId
     * @param string $group
     * @param int $monthRange
     * @return ComponentBreakdownDto[]
     */
    public function getForAllTestersAtSite(int $siteId, string $group, int $monthRange): array
    {
        return $this->getMany(ComponentBreakdownDto::class, sprintf(self::PATH_SITE_ALL_TESTERS_GROUP, $siteId,$group, $monthRange));
    }
}