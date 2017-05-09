<?php

namespace OrganisationApi\Controller;

use DvsaCommon\Enum\OrganisationSiteStatusCode;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use OrganisationApi\Service\SiteLinkService;

/**
 * Class SiteLinkController.
 */
class SiteLinkController extends AbstractDvsaRestfulController
{
    /** @var SiteLinkService */
    protected $siteLinkService;

    public function __construct(
        SiteLinkService $siteLinkService
    ) {
        $this->siteLinkService = $siteLinkService;

        $this->setIdentifierName('linkId');
    }

    public function getList()
    {
        return ApiResponse::jsonOk($this->siteLinkService->getApprovedUnlinkedSite());
    }

    public function get($linkId)
    {
        return ApiResponse::jsonOk($this->siteLinkService->get($linkId, OrganisationSiteStatusCode::ACTIVE));
    }

    public function create($data)
    {
        $orgId = $this->params('id');
        $siteNumber = $data['siteNumber'];

        return ApiResponse::jsonOk($this->siteLinkService->siteLink($orgId, $siteNumber));
    }

    public function update($linkId, $statusCode)
    {
        return ApiResponse::jsonOk($this->siteLinkService->siteChangeStatus($linkId, $statusCode));
    }
}
