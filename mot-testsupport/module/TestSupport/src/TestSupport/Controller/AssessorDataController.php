<?php

namespace TestSupport\Controller;

use DvsaCommon\Constants\Role;
use TestSupport\Helper\TestSupportAccessTokenManager;
use TestSupport\Service\AccountDataService;

/**
 * Creates User account with ASSESSMENT role for use by tests.
 *
 * Should not be deployed in production.
 */
class AssessorDataController extends BaseTestSupportRestfulController
{
    /**
     * @param mixed $data including
     *                    "diff" string to differentiate scheme management users
     *
     * @return JsonModel username of new tester
     */
    public function create($data)
    {
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);

        /** @var $accountHelper AccountDataService */
        $accountHelper = $this->getServiceLocator()->get(AccountDataService::class);

        $resultJson = $accountHelper->create($data, Role::DVSA_AREA_OFFICE_1);
        $accountHelper->addRole($resultJson->data['personId'], Role::DVSA_AREA_OFFICE_1);

        return $resultJson;
    }
}
