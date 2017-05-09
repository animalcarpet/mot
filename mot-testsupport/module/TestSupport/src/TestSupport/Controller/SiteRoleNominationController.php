<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\SiteRoleNominationService;

class SiteRoleNominationController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        $service = $this->serviceLocator->get(SiteRoleNominationService::class);

        $userId = $data['userId'];
        $siteId = $data['siteId'];
        $roleCode = $data['roleCode'];

        $result = $service->nominateUserForRoleAtSite($userId, $siteId, $roleCode);

        return TestDataResponseHelper::jsonOk($result);
    }
}
