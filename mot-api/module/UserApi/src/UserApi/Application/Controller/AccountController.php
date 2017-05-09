<?php

namespace UserApi\Application\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use UserApi\Application\Service\AccountService;

/**
 * Enables working over Person and Address resources in a single http call.
 */
class AccountController extends AbstractDvsaRestfulController
{
    public function create($data)
    {
        $service = $this->getAccountService();
        $newPersonAccountId = $service->create($data);

        return ApiResponse::jsonOk($newPersonAccountId);
    }

    /**
     * @return AccountService
     */
    protected function getAccountService()
    {
        return $this->getServiceLocator()->get(AccountService::class);
    }
}
