<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaMotApi\Controller\Validator\ReinspectionReportValidator;
use DvsaCommonApi\Model\ApiResponse;

class ReinspectionReportController extends AbstractDvsaRestfulController
{
    public function create($data)
    {
        $riValidator = new ReinspectionReportValidator($data);
        $riValidator->validate();

        return ApiResponse::jsonOk(
            [
                'outcome' => $riValidator->getOutcome(),
            ]
        );
    }
}
