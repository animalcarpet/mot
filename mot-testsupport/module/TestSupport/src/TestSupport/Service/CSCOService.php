<?php

namespace TestSupport\Service;

use TestSupport\Helper\TestSupportAccessTokenManager;
use DvsaCommon\Constants\Role;
use Zend\View\Model\JsonModel;

class CSCOService
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
     * Create a CSCO with the data supplied.
     *
     * @param array $data
     *
     * @return JsonModel
     */
    public function create(array $data)
    {
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);

        $resultJson = $this->accountDataService->create($data, Role::CUSTOMER_SERVICE_CENTRE_OPERATIVE);
        $this->accountDataService->addRole($resultJson->data['personId'], Role::CUSTOMER_SERVICE_CENTRE_OPERATIVE);

        return $resultJson;
    }
}
