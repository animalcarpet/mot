<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\National\QueryBuilder;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\QueryBuilder\ComponentBreakdownQueryBuilder;

class NationalComponentBreakdownQueryBuilder extends ComponentBreakdownQueryBuilder
{
    protected function getUseIndex()
    {
        return 'USE INDEX (`mot_test_completed_date_idx`)';
    }
}
