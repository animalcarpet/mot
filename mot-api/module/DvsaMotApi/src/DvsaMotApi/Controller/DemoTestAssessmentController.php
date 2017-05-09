<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaMotApi\Service\DemoTestAssessmentService;

class DemoTestAssessmentController extends AbstractDvsaRestfulController
{
    private $recordDemoTestService;

    public function __construct(
        DemoTestAssessmentService $recordDemoTestService
    ) {
        $this->recordDemoTestService = $recordDemoTestService;
    }

    public function create($data)
    {
        $this->recordDemoTestService->create($data);

        return ApiResponse::jsonOk([]);
    }
}
