<?php

namespace SiteApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\SiteDetailsService;

/**
 * Controller for getting / updating site's testing facilities.
 */
class SiteDetailsController extends AbstractDvsaRestfulController
{
    /**
     * @var SiteDetailsService
     */
    private $siteDetailsService;

    public function __construct(SiteDetailsService $siteDetailsService)
    {
        $this->siteDetailsService = $siteDetailsService;
    }

    public function patch($siteId, $data)
    {
        $result = $this->siteDetailsService->patch($siteId, $data);

        return ApiResponse::jsonOk($result);
    }
}
