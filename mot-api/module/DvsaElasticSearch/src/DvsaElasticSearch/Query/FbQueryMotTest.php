<?php

namespace DvsaElasticSearch\Query;

use DvsaCommon\Dto\Search\SearchResultDto;
use DvsaCommonApi\Model\SearchParam;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaElasticSearch\Model\ESDocMotTest;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\MotTestRepository;

/**
 * Class FbQueryMotTest
 *
 * I answer as a fallback for all MotTest that match the set search criteria.
 *
 * @package DvsaElasticSearch\Query
 */
class FbQueryMotTest implements IFbQuery
{
    /**
     * @param SearchParam $searchParams
     * @param array $optionalMotTestTypes
     *
     * @return array
     *
     * @throws BadRequestException
     */
    public function execute(SearchParam $searchParams, array $optionalMotTestTypes)
    {
        $result = new SearchResultDto();
        $result->setSearched($searchParams->toDto());

        /* @var MotTestRepository $motRepo */
        $motRepo = $searchParams->getRepository(MotTest::class);

        if ($searchParams->getSearchRecent()) {
            $motTests = $motRepo->getLatestMotTestsBySiteNumber($searchParams->getSiteNumber(),
                                                                $optionalMotTestTypes);
            $totalResultCount = count($motTests);
        } else {
            $motTests = $motRepo->getMotTestSearchResult($searchParams, $optionalMotTestTypes);
            $totalResultCount = $motRepo->getMotTestSearchResultCount($searchParams, $optionalMotTestTypes);
        }

        $result
            ->setData($motTests)
            ->setResultCount(count($motTests))
            ->setTotalResultCount($totalResultCount);

        $result->setData((new ESDocMotTest())->asJson($result));

        return $result;
    }
}
