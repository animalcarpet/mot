<?php

namespace TestSupport\Service;

use TestSupport\Helper\TestSupportAccessTokenManager;
use DvsaCommon\Constants\Role;
use Zend\View\Model\JsonModel;

class AreaOffice1Service
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
     * Create a AO1 with the data supplied.
     *
     * @param array $data
     *
     * @return JsonModel
     */
    public function create(array $data)
    {
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);

        $resultJson = $this->accountDataService->create($data);
        $this->accountDataService->addRole($resultJson->data['personId'], Role::DVSA_AREA_OFFICE_1);

        return $resultJson;
    }
}
