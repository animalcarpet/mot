<?php

namespace DvsaElasticSearch\Query;

use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommonApi\Model\SearchParam;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaElasticSearch\Model\ESDocMotTestLog;

/**
 * Class FbQueryMotTestLog
 *
 * I answer as a fallback for all MotTestLog that match the set search criteria.
 */
class FbQueryMotTestLog implements IFbQuery
{
    /**
     * @param SearchParam|MotTestSearchParam $searchParams     
     * @param array $optionalMotTestTypes
     *
     * @return SearchResultDto
     *
     * @throws BadRequestException
     */
    public function execute(SearchParam $searchParams, array $optionalMotTestTypes)
    {
        $resultDto = new SearchResultDto();
        $resultDto
            ->setSearched($searchParams->toDto())
            ->setIsElasticSearch(false);

        $repo = $searchParams->getRepository();

        if ($searchParams->isApiGetTotalCount()) {

            $dbResult = $repo->getMotTestLogsResultCount($searchParams);

            $resultDto->setTotalResultCount($dbResult['count']);
        }

        if ($searchParams->isApiGetData()) {

            $dbResult = $repo->getMotTestLogsResult($searchParams);

            $resultDto
                ->setResultCount(count($dbResult))
                ->setData($dbResult);

            $resultDto->setData((new ESDocMotTestLog())->asJson($resultDto));
        }

        return $resultDto;
    }
}
