<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\Repository;

use Doctrine\ORM\AbstractQuery;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Repository\ComponentStatisticsRepository;
use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\QueryBuilder\TesterComponentBreakdownQueryBuilder;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TesterComponentStatisticsRepository extends ComponentStatisticsRepository implements AutoWireableInterface
{
    const PARAM_SITE_ID = 'siteId';
    const PARAM_TESTER_ID = 'testerId';

    public function get($testerId, $siteId, $group, $year, $month)
    {
        $qb = new TesterComponentBreakdownQueryBuilder();

        $this->setDaysConfiguration($year, $month);

        return $this->getResult($qb->getSql(), [
            self::PARAM_TESTER_ID                           => $testerId,
            self::PARAM_SITE_ID                             => $siteId,
            ComponentStatisticsRepository::PARAM_GROUP      => $group,
            ComponentStatisticsRepository::PARAM_START_DATE => $this->startDate,
            ComponentStatisticsRepository::PARAM_END_DATE   => $this->endDate,
        ]);
    }

    protected function setParameters(AbstractQuery $query, $params)
    {
        parent::setParameters($query, $params);
        $query->setParameter(self::PARAM_TESTER_ID, $params[self::PARAM_TESTER_ID]);
        $query->setParameter(self::PARAM_SITE_ID, $params[self::PARAM_SITE_ID]);
    }
}