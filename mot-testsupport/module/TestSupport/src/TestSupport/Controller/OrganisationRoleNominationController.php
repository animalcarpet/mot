<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\OrganisationRoleNominationService;

class OrganisationRoleNominationController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        $service = $this->serviceLocator->get(OrganisationRoleNominationService::class);

        $userId = $data['userId'];
        $orgId = $data['orgId'];
        $roleId = $data['roleId'];


        $result = $service->nominateUser($userId, $orgId, $roleId);

        return TestDataResponseHelper::jsonOk($result);
    }
}