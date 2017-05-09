<?php

namespace SiteApi\Controller;

use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\SiteSearchService;
use Zend\View\Model\JsonModel;

/**
 * Class SiteSearchController.
 */
class SiteSearchController extends AbstractDvsaRestfulController
{
    /** @var SiteSearchService */
    private $service;

    /**
     * @param SiteSearchService $service
     */
    public function __construct(SiteSearchService $service)
    {
        $this->service = $service;
    }

    /**
     * @param array $data
     *
     * @return JsonModel
     */
    public function create($data)
    {
        return ApiResponse::jsonOk($this->service->findSites(DtoHydrator::jsonToDto($data)));
    }

    /**
     * @param mixed $number
     *
     * @return JsonModel
     */
    public function get($number)
    {
        return ApiResponse::jsonOk($this->service->findSiteByNumber($number));
    }
}
