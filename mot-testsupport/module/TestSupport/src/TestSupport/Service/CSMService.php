<?php

namespace TestSupport\Service;

use TestSupport\Helper\TestSupportAccessTokenManager;
use DvsaCommon\Constants\Role;

class CSMService
{
    /**
     * @var AccountDataService
     */
    protected $accountDataService;

    public function __construct(
        AccountDataService $accountDataService
    ) {
        $this->accountDataService = $accountDataService;
    }

    /**
     * Create a basic user with the data supplied.
     *
     * @param array $data
     *
     * @return JsonModel
     */
    public function create(array $data)
    {
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);
        $resultJson = $this->accountDataService->create($data, Role::CUSTOMER_SERVICE_MANAGER);
        $this->accountDataService->addRole($resultJson->data['personId'], Role::CUSTOMER_SERVICE_MANAGER);

        return $resultJson;
    }
}
