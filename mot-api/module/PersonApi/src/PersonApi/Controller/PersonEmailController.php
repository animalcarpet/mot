<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\DuplicateEmailCheckerService;

class PersonEmailController extends AbstractDvsaRestfulController
{
    private $duplicateEmailCheckerService;

    public function __construct(DuplicateEmailCheckerService $duplicateEmailCheckerService)
    {
        $this->duplicateEmailCheckerService = $duplicateEmailCheckerService;
    }

    public function duplicateEmailAction()
    {
        $emailRouteParam = $this->params()->fromQuery('email');

        if (empty($emailRouteParam) || is_null($emailRouteParam) || $emailRouteParam === "") {
            return ApiResponse::jsonError('Email cannot be empty.');
        }

        $isDuplicate = $this->duplicateEmailCheckerService->isEmailDuplicated($emailRouteParam);

        return ApiResponse::jsonOk(['isDuplicate' => $isDuplicate]);
    }
}