<?php

namespace SiteApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\SiteService;

class SiteNameController extends AbstractDvsaRestfulController
{
    /**
     * @var SiteService
     */
    private $service;

    /**
     * @param SiteService $service
     */
    public function __construct(SiteService $service)
    {
        $this->service = $service;
    }

    public function get($id)
    {
        $data = $this->service->getSiteName($id);

        return ApiResponse::jsonOk($data);
    }
}
