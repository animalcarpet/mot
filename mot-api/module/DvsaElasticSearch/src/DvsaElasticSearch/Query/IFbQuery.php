<?php

namespace DvsaElasticSearch\Query;
use DvsaCommonApi\Model\SearchParam;

/**
 * Interface IFbQuery
 *
 * All children od Fallback Search Query MUST implement me to be functional.
 *
 * @package DvsaElasticSearch\Query
 */
interface IFbQuery
{
    public function execute(SearchParam $searchParams, array $optionalMotTestTypes);
}
 