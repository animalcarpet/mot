<?php

namespace TestSupport\Controller;

use DvsaCommon\Enum\SiteBusinessRoleCode;
use TestSupport\Helper\TestSupportAccessTokenManager;
use TestSupport\Service\SiteUserDataService;

/**
 * Creates Site Admin for use by tests.
 *
 * Should not be deployed in production.
 */
class SiteAdminDataController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);

        /** @var SiteUserDataService $siteUser */
        $siteUser = $this->getServiceLocator()->get(SiteUserDataService::class);

        return $siteUser->create($data, SiteBusinessRoleCode::SITE_ADMIN);
    }
}
