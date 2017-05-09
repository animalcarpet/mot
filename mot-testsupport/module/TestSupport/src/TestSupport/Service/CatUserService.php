<?php

namespace TestSupport\Service;

use TestSupport\Helper\TestSupportAccessTokenManager;
use DvsaCommon\Constants\Role;

class CatUserService
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
        $resultJson = $this->accountDataService->create($data, Role::CENTRAL_ADMIN_TEAM);
        $this->accountDataService->addRole($resultJson->data['personId'], Role::CENTRAL_ADMIN_TEAM);

        return $resultJson;
    }
}
