<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\Tester\QueryBuilder;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryBuilder\ComponentBreakdownQueryBuilder;

class TesterComponentBreakdownQueryBuilder extends ComponentBreakdownQueryBuilder
{
    protected function getJoin()
    {
        return 'JOIN person person ON person.id = test.person_id
        JOIN site vts ON vts.id = test.site_id';
    }

    protected function getWhere()
    {
        return 'AND person.id = :testerId';
    }
}
