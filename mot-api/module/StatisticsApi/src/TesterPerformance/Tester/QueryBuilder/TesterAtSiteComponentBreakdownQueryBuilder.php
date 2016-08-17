<?php
namespace Dvsa\Mot\Api\StatisticsApi\TesterPerformance\Tester\QueryBuilder;

use Dvsa\Mot\Api\StatisticsApi\TesterPerformance\QueryBuilder\ComponentBreakdownQueryBuilder;

class TesterAtSiteComponentBreakdownQueryBuilder extends ComponentBreakdownQueryBuilder
{
    protected function getJoin()
    {
        return 'JOIN person person ON person.id = test.person_id
        JOIN site vts ON vts.id = test.site_id';
    }

    protected function getWhere()
    {
        return 'AND vts.id = :siteId AND person.id = :testerId
        AND EXISTS
        (
        -- the site had to have an active association to the current AE during the time when the test was performed
          SELECT * FROM organisation_site_map associationToAe
          JOIN organisation_site_status associationStatus ON associationStatus.id = associationToAe.status_id
          WHERE associationToAe.site_id = vts.id
          AND associationToAe.organisation_id = vts.organisation_id
          AND associationStatus.code NOT IN (:irrelevantAssociationCodes)
          AND associationToAe.start_date <= test.completed_date
          AND
          (
            associationToAe.end_date >= test.completed_date
            OR associationToAe.end_date IS NULL
          )
        )';
    }
}
