<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\AccountDataService;

class SiteRoleController extends BaseTestSupportRestfulController
{
    public function update($personId, $data)
    {
        /** @var $accountHelper AccountDataService */
        $accountHelper = $this->getServiceLocator()->get(AccountDataService::class);

        $siteId = $this->params()->fromRoute('site', null);
        $role = $this->params()->fromRoute('role', null);

        if (!empty($personId) && !empty($siteId) && !empty($role)) {
            $accountHelper->addSiteRole($personId, $siteId, $role);

            return TestDataResponseHelper::jsonOk(['success' => true]);
        }

        return TestDataResponseHelper::jsonError('Missing parameters');
    }
}
