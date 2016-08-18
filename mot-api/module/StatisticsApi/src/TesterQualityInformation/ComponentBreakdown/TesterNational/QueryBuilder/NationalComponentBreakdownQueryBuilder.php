<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\QueryBuilder;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryBuilder\ComponentBreakdownQueryBuilder;

class NationalComponentBreakdownQueryBuilder extends ComponentBreakdownQueryBuilder
{
    protected function getUseIndex()
    {
        return 'USE INDEX (`mot_test_completed_date_idx`)';
    }
}
