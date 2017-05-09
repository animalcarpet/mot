<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use OrganisationApi\Service\AuthorisedExaminerService;

/**
 * Returns data about AE linked with person.
 */
class AuthorisedExaminerController extends AbstractDvsaRestfulController
{
    /**
     * @var AuthorisedExaminerService
     */
    protected $authorisedExaminerService;

    public function __construct(AuthorisedExaminerService $service)
    {
        $this->authorisedExaminerService = $service;
    }

    public function get($personId)
    {
        return ApiResponse::jsonOk($this->authorisedExaminerService->getAuthorisedExaminersForPerson($personId));
    }
}
