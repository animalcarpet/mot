<?php

namespace TestSupport\Controller;

use TestSupport\Service\FinanceUserService;

class FinanceUserController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        $service = $this->getServiceLocator()->get(FinanceUserService::class);
        $resultJson = $service->create($data);

        return $resultJson;
    }
}
