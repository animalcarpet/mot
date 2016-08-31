<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\Repository;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Common\Repository\ManyGroupsStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\QueryBuilder\TesterManyGroupsStatisticsQueryBuilder;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TesterManyGroupsStatisticsRepository extends ManyGroupsStatisticsRepository implements AutoWireableInterface
{
    const PARAM_TESTER_ID = "testerId";

    public function get($testerId, $year, $month)
    {
        return parent::getByParams([
            self::PARAM_TESTER_ID => $testerId,
            self::PARAM_MONTH     => $month,
            self::PARAM_YEAR      => $year,
        ]);
    }

    protected function getSql()
    {
        return (new TesterManyGroupsStatisticsQueryBuilder())->getSql();
    }
}