<?php

namespace SiteApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\MotTestInProgressService;

/**
 * Class MotTestInProgressController.
 */
class MotTestInProgressController extends AbstractDvsaRestfulController
{
    /** @var MotTestInProgressService */
    protected $service;

    public function __construct(
        MotTestInProgressService $service
    ) {
        $this->service = $service;
    }

    public function get($siteId)
    {
        return ApiResponse::jsonOk($this->service->getAllForSite($siteId));
    }

    public function countAction()
    {
        $siteId = $this->params('id');

        return ApiResponse::jsonOk($this->service->getCountForSite($siteId));
    }
}
