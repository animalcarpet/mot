<?php

namespace SiteApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use SiteApi\Service\SiteBusinessRoleService;

/**
 * Class SiteRoleController.
 */
class SiteRoleController extends AbstractDvsaRestfulController
{
    public function getList()
    {
        $personId = $this->params()->fromRoute('personId');

        $service = $this->getSiteRoleService();

        $allRoles = $service->getListForPerson($personId);

        return ApiResponse::jsonOk($allRoles);
    }

    /**
     * @return SiteBusinessRoleService
     */
    private function getSiteRoleService()
    {
        return $this->getServiceLocator()->get(SiteBusinessRoleService::class);
    }
}
