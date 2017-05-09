<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\ClaimAccountService;

/**
 * Makes a specific user require going through claim account.
 */
class ClaimAccountController extends BaseTestSupportRestfulController
{
    public function create($data)
    {
        $service = $this->getServiceLocator()->get(ClaimAccountService::class);

        return TestDataResponseHelper::jsonOk($service->create($data));
    }
}
