<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\OrganisationRoleNominationService;

class OrganisationRoleNominationController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        /** @var OrganisationRoleNominationService $service */
        $service = $this->serviceLocator->get(OrganisationRoleNominationService::class);

        $userId = $data['userId'];
        $orgId = $data['orgId'];
        $roleCode = $data['roleCode'];

        $result = $service->nominateUser($userId, $orgId, $roleCode);

        return TestDataResponseHelper::jsonOk($result);
    }
}
