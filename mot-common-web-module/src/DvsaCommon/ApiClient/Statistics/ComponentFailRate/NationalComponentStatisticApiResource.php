<?php
namespace DvsaCommon\ApiClient\Statistics\ComponentFailRate;

use DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto\NationalComponentStatisticsDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class NationalComponentStatisticApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @param $group
     * @param $month
     * @param $year
     * @return NationalComponentStatisticsDto
     */
    public function getForDate($group, $month, $year)
    {
        return $this->getSingle(NationalComponentStatisticsDto::class, sprintf('statistic/component-fail-rate/national/group/%s/%s/%s', $group, $year, $month));
    }
}
