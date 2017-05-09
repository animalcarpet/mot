<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Model\OutputFormat;
use DvsaCommonApi\Model\SearchParam;
use DvsaEntities\DqlBuilder\SearchParamDqlBuilder;

/**
 * Class SearchRepository.
 */
trait SearchRepositoryTrait
{
    use SearchParamTrait;

    /**
     * Search the current repository using params and an output format.
     *
     * @param SearchParam $params
     * @param null        $format
     *
     * @return array
     */
    public function search(SearchParam $params, OutputFormat $format = null)
    {
        $sqlBuilder = $this->getSqlBuilder($params)->generate();

        $totalResultCount = null;
        if ($params->isApiGetTotalCount()) {
            $totalResultCount = $sqlBuilder->getSearchCountQuery()->getResult();

            $totalResultCount = (isset($totalResultCount[0][1]) ? $totalResultCount[0][1] : 0);
        }

        $data = null;
        if ($params->isApiGetData()) {
            $results = $sqlBuilder->getSearchQuery()->getResult();

            if ($format === null) {
                $data = $results;
            } else {
                $data = $format->extractItems($results);
            }
        }

        return [
            'resultCount' => (string) count($data),
            'totalResultCount' => $totalResultCount,
            'data' => $data,
            'searched' => $params->toArray(),
        ];
    }

    /**
     * @param SearchParam $params
     *
     * @throws \Exception
     *
     * @returns SearchParamDqlBuilder
     */
    abstract protected function getSqlBuilder($params);
}
