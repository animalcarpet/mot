<?php

namespace TestSupport\Service;

use TestSupport\Helper\TestSupportAccessTokenManager;
use DvsaCommon\Constants\Role;
use Zend\View\Model\JsonModel;

class GVTSTesterService
{
    /**
     * @var AccountDataService
     */
    protected $accountDataService;

    public function __construct(AccountDataService $accountDataService)
    {
        $this->accountDataService = $accountDataService;
    }

    /**
     * Create a GVTS tester with the data supplied.
     *
     * @param array $data
     *
     * @return JsonModel
     */
    public function create(array $data)
    {
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);

        $resultJson = $this->accountDataService->create($data);
        $this->accountDataService->addRole($resultJson->data['personId'], Role::GVTS_TESTER);

        return $resultJson;
    }
}
