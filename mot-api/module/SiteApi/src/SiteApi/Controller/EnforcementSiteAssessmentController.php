<?php

namespace SiteApi\Controller;

use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\EnforcementSiteAssessmentService;

class EnforcementSiteAssessmentController extends AbstractDvsaRestfulController
{
    /** @var EnforcementSiteAssessmentService */
    private $riskAssessmentService;

    public function __construct(EnforcementSiteAssessmentService $riskAssessmentService)
    {
        $this->riskAssessmentService = $riskAssessmentService;
    }

    public function get($id)
    {
        $dto = $this->riskAssessmentService->getRiskAssessment($id);

        return ApiResponse::jsonOk($dto);
    }

    public function create($data)
    {
        $dto = DtoHydrator::jsonToDto($data);
        $id = $this->riskAssessmentService->createRiskAssessment($dto);

        return ApiResponse::jsonOk($id);
    }
}
