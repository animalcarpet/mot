<?php

namespace OrganisationApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use OrganisationApi\Service\AuthorisedExaminerStatusService;

class AuthorisedExaminerStatusController extends AbstractDvsaRestfulController
{
    /**
     * @var AuthorisedExaminerStatusService
     */
    private $service;

    /**
     * @param AuthorisedExaminerStatusService $service
     */
    public function __construct(AuthorisedExaminerStatusService $service)
    {
        $this->service = $service;
    }

    public function getAreaOfficesAction()
    {
        return ApiResponse::jsonOk($this->service->getAllAreaOffices());
    }
}
