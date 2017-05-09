<?php

namespace TestSupport\Service;

use TestSupport\Helper\TestSupportAccessTokenManager;
use DvsaCommon\Constants\Role;
use Zend\View\Model\JsonModel;

class DVLAOperativeService
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
     * Create a DVLA with the data supplied.
     *
     * @param array $data
     *
     * @return JsonModel
     */
    public function create(array $data)
    {
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);

        $resultJson = $this->accountDataService->create($data, Role::DVLA_OPERATIVE);
        $this->accountDataService->addRole($resultJson->data['personId'], Role::DVLA_OPERATIVE);

        return $resultJson;
    }
}
