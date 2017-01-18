<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\TesterNational\QueryBuilder;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\ComponentBreakdown\Common\QueryBuilder\ComponentBreakdownQueryBuilder;

class NationalComponentBreakdownQueryBuilder extends ComponentBreakdownQueryBuilder
{
    protected function getUseIndex()
    {
        return 'USE INDEX (`ix_mot_test_current_completed_date`)';
    }
}
