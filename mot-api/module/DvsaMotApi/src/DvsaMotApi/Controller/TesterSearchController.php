<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

/**
 * Move to the following syntax for searches..
 *
 * http://mot-api:8080/search/mot-test/bristol;count:10;start:20/data-tables
 * http://mot-api:8080/search/tester/bristol;count:10;start:20/data-tables
 * http://mot-api:8080/search/vts/bristol;count:10;start:20/data-tables
 * http://mot-api:8080/search/vts/bristol;count:10;start:20/type-ahead
 * http://mot-api:8080/search/vts/bristol;count:10;start:20/data-objects
 *
 *
 *
 * Class TesterSearchController
 */
class TesterSearchController extends AbstractDvsaRestfulController
{
    const SEARCH_PARAMETER = 'search';

    const SEARCH_REQUIRED_DISPLAY_MESSAGE = 'You need to enter a search value to perform the search';
    const SEARCH_INVALID_DATA_MESSAGE = 'search: non alphanumeric characters found';
    const SEARCH_INVALID_DATA_DISPLAY_MESSAGE = 'search contain alphanumeric characters only';

    public function getList()
    {
        try {
            $service = $this->getServiceLocator()->get('TesterSearchService');

            $searchParams = $this->buildSearchParams($service);

            return ApiResponse::jsonOk(
                $service->search(
                    $searchParams,
                    $service->getOutputFormat($searchParams)
                )
            );
        } catch (\UnexpectedValueException $e) {
            return $this->returnBadRequestResponseModel(
                $e->getMessage(),
                self::ERROR_CODE_REQUIRED,
                self::SEARCH_REQUIRED_DISPLAY_MESSAGE
            );
        }
    }

    /**
     * Build the search params from the current request.
     *
     * @param $service
     *
     * @return mixed
     */
    protected function buildSearchParams($service)
    {
        $request = $this->getRequest();
        $search = (string) $request->getQuery(self::SEARCH_PARAMETER);

        $searchParams = $service
            ->getSearchParams()
            ->setSearch($search)
            ->loadStandardDataTableValuesFromRequest($request);

        return $searchParams;
    }
}
