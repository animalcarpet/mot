<?php

namespace OrganisationApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use OrganisationApi\Service\SiteService;

/**
 * Class SiteController.
 */
class SiteController extends AbstractDvsaRestfulController
{
    /** @var SiteService */
    protected $siteService;

    public function __construct(
        SiteService $siteService
    ) {
        $this->siteService = $siteService;

        $this->setIdentifierName('siteNumber');
    }

    public function getList()
    {
        $organisationId = $this->params('id');

        return ApiResponse::jsonOk($this->siteService->getListForOrganisation($organisationId));
    }
}
