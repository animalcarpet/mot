<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Tester\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\Repository\ComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\QueryBuilder\TesterComponentBreakdownQueryBuilder;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TesterComponentStatisticsRepository extends ComponentStatisticsRepository implements AutoWireableInterface
{
    const PARAM_TESTER_ID = 'testerId';

    public function get($testerId, $group, $year, $month)
    {
        $qb = new TesterComponentBreakdownQueryBuilder();

        $this->setDaysConfiguration($year, $month);

        return $this->getResult($qb->getSql(), [
            self::PARAM_TESTER_ID => $testerId,
            ComponentStatisticsRepository::PARAM_GROUP => $group,
            ComponentStatisticsRepository::PARAM_START_DATE => $this->startDate,
            ComponentStatisticsRepository::PARAM_END_DATE => $this->endDate,
        ]);
    }
}
