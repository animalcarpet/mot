<?php

namespace VehicleApi\Helper;

use Zend\Stdlib\RequestInterface as Request;

/**
 * Class VehicleService.
 */
class VehicleSearchParams
{
    const SEARCH_QUERY_PARAMETER = 'search';
    const SEARCH_TYPE_QUERY_PARAMETER = 'type';

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getSearchParam()
    {
        return $this->request->getQuery(self::SEARCH_QUERY_PARAMETER, '');
    }

    public function getSearchTypeParam()
    {
        return $this->request->getQuery(self::SEARCH_TYPE_QUERY_PARAMETER, '');
    }
}
