<?php

namespace TestSupport\Service;

use TestSupport\Helper\TestSupportAccessTokenManager;
use DvsaCommon\Constants\Role;

class VM10519UserService
{
    protected $accountDataService;

    public function __construct(AccountDataService $accountDataService)
    {
        $this->accountDataService = $accountDataService;
    }

    public function create(array $data)
    {
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);
        $resultJson = $this->accountDataService->create($data, 'VM-10519-USER');
        $this->accountDataService->addRole($resultJson->data['personId'], 'VM-10519-USER');
        $this->accountDataService->addRole($resultJson->data['personId'], Role::VEHICLE_EXAMINER);

        return $resultJson;
    }
}
