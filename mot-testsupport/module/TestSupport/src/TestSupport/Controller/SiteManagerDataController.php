<?php

namespace TestSupport\Controller;

use DvsaCommon\Enum\SiteBusinessRoleCode;
use TestSupport\Helper\TestSupportAccessTokenManager;
use TestSupport\Service\SiteUserDataService;

/**
 * Creates Site Managers for use by tests.
 *
 * Should not be deployed in production.
 */
class SiteManagerDataController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);

        /** @var SiteUserDataService $siteUser */
        $siteUser = $this->getServiceLocator()->get(SiteUserDataService::class);

        return $siteUser->create($data, SiteBusinessRoleCode::SITE_MANAGER);
    }
}
