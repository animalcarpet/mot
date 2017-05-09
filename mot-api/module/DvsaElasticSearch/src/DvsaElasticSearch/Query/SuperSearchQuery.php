<?php

namespace DvsaElasticSearch\Query;

use DvsaCommonApi\Model\SearchParam;

/**
 * Class SuperSearchQuery.
 *
 * I answer all the type of search and redirect to the good service.
 */
class SuperSearchQuery
{
    /**
     * @param SearchParam $searchParams
     * @param IFbQuery    $databaseFallBack
     * @param array       $optionalMotTestTypes
     *
     * @return
     */
    public static function execute($searchParams, $databaseFallBack, array $optionalMotTestTypes = [])
    {
        return $databaseFallBack->execute($searchParams, $optionalMotTestTypes);
    }
}
