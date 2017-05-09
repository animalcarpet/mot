<?php

namespace OrganisationApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use OrganisationApi\Service\AuthorisedExaminerPrincipalService;

/**
 * Controller for an AuthorisedExaminer's AuthorisedExaminerPrincipal related API calls.
 */
class AuthorisedExaminerPrincipalController extends AbstractDvsaRestfulController
{
    public function getList()
    {
        $authorisedExaminerId = $this->params()->fromRoute('authorisedExaminerId');

        $service = $this->getAuthorisedExaminerPrincipalService();

        return ApiResponse::jsonOk($service->getForAuthorisedExaminer($authorisedExaminerId));
    }

    public function get($principalId)
    {
        $authorisedExaminerId = $this->params()->fromRoute('authorisedExaminerId');

        $service = $this->getAuthorisedExaminerPrincipalService();

        return ApiResponse::jsonOk($service->getForAuthorisedExaminerById($authorisedExaminerId, $principalId));
    }

    public function delete($authorisedExaminerPrincipalId)
    {
        $authorisedExaminerId = $this->params()->fromRoute('authorisedExaminerId');

        $service = $this->getAuthorisedExaminerPrincipalService();

        $service->deletePrincipalForAuthorisedExaminer($authorisedExaminerId, $authorisedExaminerPrincipalId);

        return ApiResponse::jsonOk();
    }

    public function create($data)
    {
        $authorisedExaminerId = $this->params()->fromRoute('authorisedExaminerId');

        $service = $this->getAuthorisedExaminerPrincipalService();

        return ApiResponse::jsonOk($service->createForAuthorisedExaminer($authorisedExaminerId, $data));
    }

    /**
     * todo WK/PT: inject the service by constructor.
     *
     * @return AuthorisedExaminerPrincipalService
     */
    private function getAuthorisedExaminerPrincipalService()
    {
        return $this->getServiceLocator()->get(AuthorisedExaminerPrincipalService::class);
    }
}
