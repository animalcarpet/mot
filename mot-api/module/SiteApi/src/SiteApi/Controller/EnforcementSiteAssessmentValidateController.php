<?php

namespace SiteApi\Controller;

use DvsaCommon\Utility\DtoHydrator;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\EnforcementSiteAssessmentService;

class EnforcementSiteAssessmentValidateController extends AbstractDvsaRestfulController
{
    /** @var EnforcementSiteAssessmentService */
    private $riskAssessmentService;

    public function __construct(EnforcementSiteAssessmentService $riskAssessmentService)
    {
        $this->riskAssessmentService = $riskAssessmentService;
    }

    public function create($data)
    {
        $dto = DtoHydrator::jsonToDto($data);
        $validatedDto = $this->riskAssessmentService->validateRiskAssessment($dto);

        return ApiResponse::jsonOk($validatedDto);
    }
}
