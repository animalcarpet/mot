<?php

namespace PersonApi\Controller;

use DvsaAuthorisation\Service\UserRoleService;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;

class PersonPendingRoleController extends AbstractDvsaRestfulController
{
    /**
     * @var UserRoleService
     */
    private $userRoleService;

    /**
     * @param UserRoleService $userRoleService
     */
    public function __construct(UserRoleService $userRoleService)
    {
        $this->userRoleService = $userRoleService;
    }

    public function get($personId)
    {
        $roles = $this->userRoleService->getPendingRolesForPerson($personId);

        return ApiResponse::jsonOk($roles);
    }
}
