<?php

namespace TestSupport\Service;

use TestSupport\Helper\TestSupportAccessTokenManager;
use Zend\View\Model\JsonModel;

class VM10619RoleManagementUpgradeService
{
    /**
     * @var AccountDataService
     */
    protected $accountDataService;

    public function __construct(AccountDataService $accountDataService)
    {
        $this->accountDataService = $accountDataService;
    }

    public function create(array $data)
    {
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);
        $this->accountDataService->addRole($data['personId'], 'VM-10619-USER');

        return new JsonModel([]);
    }
}
