<?php

namespace OrganisationApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use OrganisationApi\Service\OrganisationRoleService;

/**
 * Class OrganisationRoleController.
 */
class OrganisationRoleController extends AbstractDvsaRestfulController
{
    public function getList()
    {
        $organisationId = $this->params()->fromRoute('organisationId');
        $nomineeId = $this->params()->fromRoute('personId');
        $service = $this->getOrganisationRoleService();

        return ApiResponse::jsonOk($service->getListForPerson($organisationId, $nomineeId));
    }

    /**
     * @return OrganisationRoleService
     */
    private function getOrganisationRoleService()
    {
        return $this->getServiceLocator()->get(OrganisationRoleService::class);
    }
}
